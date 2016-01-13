<?php

namespace HungerGames\Command;

use HungerGames\HungerGames;
use pocketmine\command\Command;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\level\Position;

use HungerGames\ArenaManager;



class AddLobbyCommand extends Command implements PluginIdentifiableCommand{

    private $arenaManager;
    private $plugin;
    public $commandName = "addlobby";

    public function __construct(HungerGames $plugin, ArenaManager $arenaManager){
    parent::__construct($this->commandName, "Create the lobby spawn");
    $this->setUsage("/$this->commandName");
    $this->command = $this->commandName;
        $this->plugin = $plugin;
    $this->arenaManager = $arenaManager;
}

    public function getPlugin(){
        return $this->plugin;
    }

    public function execute(CommandSender $sender, $label,  array $params) {
        if(!$this->plugin->isEnabled()){
            return false;
        }

        if(!$sender instanceof Player){
            $sender->sendMessage("Please use the command in-game");
            return true;
        }

        if ($sender->hasPermission("hungergames.addlobby")) {
            $this->arenaManager->setLobbySpawn($sender->getPosition());
            return true;
        }
    }
}