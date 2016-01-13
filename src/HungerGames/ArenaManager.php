<?php

namespace HungerGames;

use HungerGames\Utils\PluginUtils;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\block\Block;
use pocketmine\tile\Chest;
use pocketmine\tile\Tile;
use pocketmine\item\Item;
use pocketmine\inventory\ChestInventory;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\event\Event;

use HungerGames\Command\ChestPlaceCommand;
use HungerGames\HungerGames;
use HungerGames\Model\Arena;

class ArenaManager {

    public $ChestPlacedCMDIssuer;

    /**
     * @var Arena[]
     */
    public $arenas = array();

    /**
     * @var Tile[]
     */
    public $chests = array();

    /** @var  Config */
    private $config;

    /**
     * Initialize the arenas and the chests
     * @param Config $config
     */
    public function init(Config $config) {
        $this->config = $config;
        if(!$this->config->arenas) {
            $this->config->set('arenas', []);
            $this->config->save();
            $configArenas = [];
        }
        else {
            $configArenas = $this->config->arenas;
        }

        if (!$this->config->chests) {
            $this->config->set('chests', []);
            $this->config->save();
            $configChests = [];
        }
        else {
            $configChests = $this->config->chests;
        }

        $this->parseArenas($configArenas);
        $this->parseChests($configChests);
    }

    /**
     * Adds arenas from config
     * @param $configArenas
     */
    public function parseArenas($configArenas) {
        foreach ($configArenas as $arena) {
            $id = $arena['id'];
            $capacity = $arena['capacity'];
            $spawnpoints = $arena['spawnpoints'];
            if(isset($spawnpoints[0])) {
                foreach ($spawnpoints as $spawn) {
                    $x = $spawn['x'];
                    $y = $spawn['y'];
                    $z = $spawn['z'];
                    $levelName = $spawn['level'];
                    $level = Server::getInstance()->getLevelByName($levelName);
                    $pos = new Position($x, $y, $z, $level);
                    array_push($spawnpoints, $pos);
                }
            }
            $newArena = new Arena($id, $capacity, $spawnpoints, $this);
            array_push($this->arenas, $newArena);
        }
        PluginUtils::consoleLog("Arenas loaded");
    }

    /**
     * Adds chests from config
     * @param array $configChests
     */
    public function parseChests($configChests) {
        foreach($configChests as $chest) {
            $id = $chest['id'];
            $x = $chest['x'];
            $y = $chest['y'];
            $z = $chest['z'];
            $level = Server::getInstance()->getLevel($chest['level']);
            $position = new Position($x, $y, $z, $level);

            $chestTile = $level->getTile($position);
            array_push($this->chests, $chestTile);
        }
        PluginUtils::consoleLog("Resettable chests loaded");
    }

    /**
     * Runs on chest placement, checks if the user is intending to set a resettable chest
     * @param BlockPlaceEvent $event
     */
    public function onChestPlaced(BlockPlaceEvent $event) {
        if ($this->ChestPlacedCMDIssuer != null) {
            if($this->ChestPlacedCMDIssuer == $event->getPlayer()){
                $this->addChest($event->getBlock());
                $event->getPlayer()->sendMessage(TextFormat::DARK_BLUE . "[HungerGames] " . TextFormat::WHITE . "Resettable chest added");
            }
        }
    }

    /**
     * Set issuer of ChestPlace command
     * @param Player $sender
     */
    public function setChestPlacedIssuer(Player $sender) {
        $this->ChestPlacedCMDIssuer = $sender;
    }

    /**
     * @param $id
     * @return Arena $arena
     */
    public function getArenaById($id) {
        foreach ($this->arenas as $arena) {
            $Id = $arena->getId();
            if ($id = $Id) {
                return $arena;
            }
        }
    }

    /**
     * Adds a resettable chest
     * @param Block $chest
     */
    public function addChest(Block $chest){
        $chests = $this->config->chests;
        $chests[count($this->chests)] = [
            'id' => $chest->getId(),
            'x' => $chest->getX(),
            'y' => $chest->getY(),
            'z' => $chest->getZ(),
            'level' => $chest->getLevel()->getId()];
        $this->config->set("chests", $chests);
        $this->config->save();
        array_push($this->chests, $chest);
    }

    /**
     * Gets the Arena the player is in
     * @param Player $player
     * @return Arena
     */
    public function getPlayerArena(Player $player) {
        $result = null;
        foreach($this->arenas as $arena) {
            if (!empty($arena->players)) {
                foreach ($arena->getPlayers() as $arenaPlayer) {
                    if ($player == $arenaPlayer) {
                        $result = $arena;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Adds a player to an arena
     * @param Player $player
     */
    public function spawnPlayerToArena(Player $player) {
        if ($this->getPlayerArena($player) == null) {
            foreach($this->arenas as $arena) {
                if (!$arena->inUse){
                    if (count($arena->players) != $arena->capacity && count($arena->spawnpoints) >= count($arena->players)) {
                        foreach($arena->spawnpoints as $spawn) {       //What the heck is this
                            if(!in_array($spawn, $arena->usedSpawnpoints)) {
                                $spawnString = json_encode($spawn);
                                $spawnArray = json_decode($spawnString, true);
                                $vector = new Vector3($spawnArray['x'], $spawnArray['y'], $spawnArray['z']);
                                $pos = Position::fromObject($vector, Server::getInstance()->getLevelByName($spawnArray['level']));
                                $player->teleport($pos);
                                $player->setGamemode(0);
                                array_push($arena->players, $player);
                                $arena->playerSpawns[$player->getName()] = $pos;
                                $arena->usedSpawnpoints[] = $spawn;
                                break 2;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Removes a player from the arena array
     * @param Player $player
     */
    public function removePlayerFromArena(Player $player) {
        $arena = $this->getPlayerArena($player);
        if ($arena != null) { //redundant
            //remove the player from the arena's player array
            $playerArray = array($player);
            $newPlayersArray = array_diff($arena->players, $playerArray);
            $arena->players = $newPlayersArray; // DEBUG this might not work
        }
    }

    public function getLobbySpawn() {
        if (isset($this->config->lobby)) {
            $lobby = $this->config->lobby;
            $position = new Position($lobby['x'], $lobby['y'], $lobby['z'], Server::getInstance()->getLevelByName($lobby['level']));
            return $position;
        }
    }

    public function setLobbySpawn(Position $pos) {
        $this->config->set('lobby', array('x' => $pos->getX(), 'y' => $pos->getY(), 'z' => $pos->getZ(), 'level' => $pos->getLevel()->getName()));
        $this->config->save();
    }

    public function onPlayerDeath(Player $player, Event $event) {
        if ($this->getPlayerArena($player) != null) {
            $player->setGamemode(3);
            $player->setSpawn($this->getPlayerArena($player)->playerSpawns[$player->getName()]); // player's original spawnpoint
            $player->sendMessage(TextFormat::DARK_BLUE . "[HungerGames]" . TextFormat::WHITE . " You died, you are now in spectator mode");
            $player->getInventory()->clearAll();
            $this->removePlayerFromArena($player);
        }
    }

    /**
     * Creates a new spawnpoint at a position
     * @param Position $position
     * @param $id
     */
    public function addSpawnpoint(Position $position, Player $sender, $id) {

        //create an array for the spawnpoint
        $spawnpoint = array(
            'x' => $position->getX(),
            'y' => $position->getY(),
            'z' => $position->getZ(),
            'level' => $position->getLevel()->getName()
        );

        for($i=0;$i<count($this->config->arenas);$i++) {
            $arenas = $this->config->arenas;
            $arena = $arenas[$i];
            if ($arena['id'] == $id) {
                $arena['spawnpoints'][] = $spawnpoint;
                PluginUtils::consoleLog(json_encode($arena['spawnpoints'])); //debug
                $arenas[$i] = $arena;
                $this->config->set('arenas', $arenas);
                $this->config->save();
                $sender->sendMessage(TextFormat::DARK_BLUE . "[HungerGames]" . TextFormat::WHITE . " Spawn added!");
                $arenaSet = true;
            }
        }
        if (!isset($arenaSet)) {
            $sender->sendMessage(TextFormat::DARK_BLUE. "[HungerGames]" . TextFormat::WHITE . " This arena id does not exist");
        }
    }

    /**
     * Add a new arena
     * @param $id
     * @param $capacity
     */
    public function addArena($id, $capacity) {
        $newArena = new Arena($id, $capacity, array(), $this);
        array_push($this->arenas, $newArena);

        $arenaArray = array('id' => $id, 'capacity' => $capacity, 'spawnpoints' => array());

        $arenas = $this->config->get('arenas');
        $arenas[count($arenas)] = $arenaArray;

        $this->config->set('arenas', $arenas);
        $this->config->save();
    }

    /**
     * Refreshes all resettable chests
     */
    public function refreshChests() {
        foreach($this->chests as $chest) {
            if($chest instanceof Chest) {   //pocketmine\tile\Chest
                if (!$this->config->chestitems) {
                    $this->config->set('chestitems', []);
                    $this->config->save();
                }
                else {
                    $items = $this->config->chestitems;
                    $inventory = $chest->getInventory();
                    $inventory->clearAll();
                    for ($i = 0; $i < 10; $i++) {
                        $inventory->setItem(rand(1, 27), Item::get($items[rand(0, count($items) -1)]));
                    }
                }
            }
        }
        Server::getInstance()->broadcastTip(TextFormat::DARK_BLUE . "[HungerGames]" . TextFormat::WHITE . " Chests have been refilled", $this->getPlayers());
    }

    /**
     * Gets all playing players
     * @return array
     */
    public function getPlayers()
    {
        $playerArray = array();
        foreach ($this->arenas as $arena) {
            $players = $arena->getPlayers();
            foreach ($players as $player) {
                $playerArray[] = $player;
            }
        }
        return $playerArray;
    }


    /**
     * Reset the arena
     * @param Arena $arena
     */
    public function clearArena(Arena $arena) {
        $arena->reset();
        foreach ($arena->players as $player) {
            $player->teleport($this->getLobbySpawn());
            $player->removeAllEffects();
        }
    }



}