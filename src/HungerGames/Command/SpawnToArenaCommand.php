<?php

namespace HungerGames\Command;

use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\command\CommandSender;

use HungerGames\ArenaManager;
use HungerGames\HungerGames;


class SpawnToArenaCommand extends Command implements PluginIdentifiableCommand{

    private $plugin;
    private $arenaManager;
    public $commandName = "spawnmatch";
    public $commandIssuer;

    public function __construct(HungerGames $plugin, ArenaManager $arenaManager) {
        parent::__construct($this->commandName, "Spawn to an open arena");
        $this->setUsage("/$this->commandName");

        $this->plugin = $plugin;
        $this->arenaManager = $arenaManager;
    }

    public function getPlugin(){
        return $this->plugin;
    }

    public function execute(CommandSender $sender, $label, array $params)
    {
        if (!$this->plugin->isEnabled()) {
            return false;
        }

        if (!$sender instanceof Player) {
            $sender->sendMessage("Please use the command in-game");
            return true;
        }

        else {
            $this->arenaManager->spawnPlayerToArena($sender->getPlayer());
        }
    }
}