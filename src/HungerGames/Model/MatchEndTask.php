<?php

namespace HungerGames\Model;

use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\Plugin;
use pocketmine\Server;

class MatchEndTask extends PluginTask{

    const COUNTDOWN_DURATION = 5;

    /** @var  Arena */
    private $arena;
    private $countdownVal;

    public function __construct(Plugin $owner, Arena $arena) {
        parent::__construct($owner);
        $this->arena = $arena;
        $this->countdownVal = CountdownToStart::COUNTDOWN_DURATION;
    }

    public function onRun($currentTick){
        Server::getInstance()->broadcastTip(TextFormat::DARK_BLUE . "[HungerGames]" . TextFormat::WHITE . " Match will end in " . $this->countdownVal, $this->arena->getPlayers());
        $this->countdownVal--;

        if ($this->countdownVal == 0) {
            $this->arena->onMatchEnd();
        }

    }
}