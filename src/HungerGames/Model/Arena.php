<?php

namespace HungerGames\Model;

use HungerGames\Utils\PluginUtils;
use HungerGames\Model\CountdownToStart;
use HungerGames\HungerGames;
use HungerGames\ArenaManager;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;

/** Arena object for potential mutli-arena implementation */
class Arena {

    public $inUse = false;

    public $matchStarted = false;

    /**
     * @var ArenaManager
     */
    public $arenaManager;

    public $ID;

    public $capacity;

    public $playerCount;

    public $playerSpawns = array();

    /**
     * @var Player[]
     */
    public $players = array();

    /**
     * @var Position[]
     */
    public $spawnpoints = array();

    /**
     * @var Position[]
     */
    public $usedSpawnpoints = array();

    private $countdownTaskHandler;
    private $taskHandler;

    const DURATION = 600;

    public function __construct($Id, $capacity, $spawnpoints, ArenaManager $arenaManager) {
        $this->ID = $Id;
        $this->capacity = $capacity;
        $this->spawnpoints = $spawnpoints;
        $this->inUse = FALSE;
        $this->playerCount = 0;
        $this->arenaManager = $arenaManager;
    }

    /**
     * Gets the ID of the arena
     * @return int
     */
    public function getId() {
        return $this->ID;
    }

    /**
     * Sets the ID of the arena
     * @param int $Id
     */
    public function setID($Id) {
        $this->ID = $Id;
    }

    public function setInUse($inUse){
        $this->inUse = $inUse;
    }

    public function getInUse() {
        return $this->inUse;
    }

    public function getPlayers() {
        return $this->players;
    }

    public function addPlayer(Player $player) {
        array_push($this->players, $player);
    }

    public function abortMatch() {
        Server::getInstance()->getScheduler()->cancelTask($this->countdownTaskHandler);
    }

    public function startMatch() {

        $task = new CountdownToStart(HungerGames::getInstance(), $this);
        $this->countdownTaskHandler= Server::getInstance()->getScheduler()->scheduleDelayedRepeatingTask($task, 20, 20)->getTaskId();

        $endTask = new RoundCheckTask(HungerGames::getInstance(), $this);
        $this->taskHandler = Server::getInstance()->getInstance()->getScheduler()->scheduleDelayedTask($endTask, self::DURATION * 20);
    }

    public function onMatchEnd() {
        //remaining players are winners
        $winners = $this->players;
        Server::getInstance()->getScheduler()->cancelTask($this->taskHandler->getTaskId());
        Server::getInstance()->broadcastMessage(TextFormat::DARK_BLUE . "[HungerGames]" . TextFormat::WHITE . " Congratulations!  You are a winner", $winners);

        $this->arenaManager->clearArena($this);
    }

    public function onStartCountdownEnd() {
        Server::getInstance()->getScheduler()->cancelTask($this->countdownTaskHandler);
        $this->broadcastTip("GO!");
        $this->arenaManager->refreshChests();

    }

    public function isOnline(Player $player) {
        if ($player->isOnline()) {
            return true;
        }

        else {
            return false;
        }
    }

    public function broadcastTip($tip) {
        Server::getInstance()->broadcastTip(TextFormat::DARK_BLUE . "[HungerGames]" . TextFormat::WHITE . " " . $tip, $this->players);
    }


    /**
     * Reset arena
     */
    public function reset() {
        foreach($this->players as $player) {
            $player->getInventory()->setItemInHand(new Item(Item::AIR,0,0));
            $player->getInventory()->clearAll();
            $player->getInventory()->sendArmorContents($player);
            $player->getInventory()->sendContents($player);
            $player->getInventory()->sendHeldItem($player);
        }
        $this->inUse = false;
        $this->players = null;
        $this->matchStarted = false;
        $this->playerSpawns = null;
    }
}