<?php

namespace HungerGames;

use HungerGames\Utils\PluginUtils;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\Server;

/**
 * Class EventManager
 * @package HungerGames
 */
class EventManager implements Listener{

    private $arenaManager;

    public function __construct(ArenaManager $arenaManager) {
        $this->arenaManager = $arenaManager;
    }

    public function onBlockPlace(BlockPlaceEvent $event) {
        $block = $event->getBlock()->getName();
        if($block == "Chest") {
            $this->arenaManager->onChestPlaced($event);
        }
    }

    public function onPlayerDeath(PlayerDeathEvent $event) {
        if ($event->getEntity() instanceof Player) {
            $this->arenaManager->onPlayerDeath($event->getEntity(), $event);
        }
    }

    /**
     * Checks if the player is in an arena, and if the game hasn't started yet, stops that player from moving
     * @param PlayerMoveEvent $event
     */
    public function onPlayerMove(PlayerMoveEvent $event) {
        $arena = $this->arenaManager->getPlayerArena($event->getPlayer());
        if($arena != null) { // if the player is in an arena
            if (!$arena->inUse) { //arena is not in use (match hasn't started)
                $event->setCancelled(true);
            }
        }
    }

    public function onPlayerQuit(PlayerQuitEvent $event) {
        $this->arenaManager->removePlayerFromArena($event->getPlayer());
    }

    public function onPlayerJoin(PlayerLoginEvent $event) {
        $lobby = $this->arenaManager->getLobbySpawn();
        if (isset($lobby)) {
            $event->getPlayer()->getInventory()->clearAll();
            $event->getPlayer()->teleport($lobby);
        }
        else {
            PluginUtils::consoleLog("Lobby has not been set");
        }
        $event->getPlayer()->setGamemode(0);
    }
}