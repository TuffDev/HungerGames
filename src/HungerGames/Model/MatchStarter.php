<?php

namespace HungerGames\Model;

use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\Plugin;
use pocketmine\Server;

use HungerGames\ArenaManager;

class MatchStarter extends PluginTask {

    /** @var  ArenaManager */
    public $arenaManager;

    public function __construct(Plugin $owner, ArenaManager $arenaManager) {
        parent::__construct($owner);
        $this->arenaManager = $arenaManager;
    }

    public function onRun($currentTick) {
        foreach($this->arenaManager->arenas as $arena) {
            if (count($arena->players) == $arena->capacity && !$arena->inUse && !$arena->matchStarted) {
                $arena->startMatch();
                $arena->matchStarted = true;
            }
            if (count($arena->players) == 0 && $arena->inUse) {
                $arena->arenaManager->clearArena($arena);
            }
        }
    }

}