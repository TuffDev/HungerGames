<?php

namespace HungerGames\Model;

use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;
use pocketmine\server;
use pocketmine\plugin\Plugin;

use HungerGames\ArenaManager;


class ChestRefreshTask extends PluginTask{

    /** @var  ArenaManager */
    public $arenaManager;

    public function __construct(Plugin $owner, ArenaManager $arenaManager) {
        parent::__construct($owner);
        $this->arenaManager = $arenaManager;
    }

    public function onRun($currentTick) {
        $this->arenaManager->refreshChests();
        Server::getInstance()->broadcastMessage(TextFormat::DARK_BLUE . "[HungerGames]" . TextFormat::WHITE . " Chests have been reset!", $this->arenaManager->getPlayers());
    }
}