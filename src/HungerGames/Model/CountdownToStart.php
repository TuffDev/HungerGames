<?php

namespace HungerGames\Model;

use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\Plugin;
use pocketmine\Server;

use HungerGames\Model\Arena;

class CountdownToStart extends PluginTask {

    const COUNTDOWN_DURATION = 10;

    /** @var  Arena */
    private $arena;
    private $countdownVal = 10; // initial value

    public function __construct(Plugin $owner, Arena $arena) {
        parent::__construct($owner);
        $this->arena = $arena;
        $this->countdownVal = CountdownToStart::COUNTDOWN_DURATION;
    }

    public function onRun($currentTick) {
        $this->arena->setInUse(true);

        $playersOnline = true;
        foreach($this->arena->players as $player) {
            if (!$this->arena->isOnline($player)) {
                $playersOnline = false;
            }
        }

        if ($playersOnline && $this->countdownVal >= 0) {
            $this->arena->broadcastTip("Match starts in: " . $this->countdownVal);
            $this->arena->inUse = false;
            $this->countdownVal--;
        }

        else if (!$playersOnline){
            $this->arena->abortMatch();
        }

        if($this->countdownVal <= 0) {
            $this->arena->inUse = true;
            $this->arena->onStartCountdownEnd();
            $this->arena->broadcastTip("GO!");
        }



    }
}