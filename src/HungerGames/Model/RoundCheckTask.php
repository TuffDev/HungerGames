<?php

namespace HungerGames\Model;

use HungerGames\HungerGames;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\Plugin;
use pocketmine\Server;

use HungerGames\Model\Arena;

class RoundCheckTask extends PluginTask {

    /**
     * @var Arena
     */
    public $arena;

    public function __construct(Plugin $owner, Arena $arena) {
        parent::__construct($owner);
        $this->arena = $arena;
    }

    public function onRun($currentTick){
        $task = new MatchEndTask(HungerGames::getInstance(), $this->arena);
        Server::getInstance()->getScheduler()->scheduleDelayedRepeatingTask($task, 20, 20);
    }
}