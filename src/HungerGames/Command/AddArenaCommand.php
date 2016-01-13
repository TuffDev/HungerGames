<?php

namespace HungerGames\Command;

use pocketmine\command\Command;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;

use HungerGames\HungerGames;
use HungerGames\ArenaManager;
use pocketmine\utils\TextFormat;

class AddArenaCommand extends Command implements PluginIdentifiableCommand{

    private $plugin;
    private $arenaManager;
    public $commandName = "newarena";

    public function __construct(HungerGames $plugin, ArenaManager $arenaManager){
        parent::__construct($this->commandName, "Create a new arena");
        $this->setUsage("/$this->commandName" . " [id] [capacity]");
        $this->command = $this->commandName;

        $this->plugin = $plugin;
        $this->arenaManager = $arenaManager;
    }

    public function getPlugin() {
        return $this->plugin;
    }

    public function execute(CommandSender $sender, $label, array $params){
        if(!$this->plugin->isEnabled()){
            return false;
        }

        if($sender->hasPermission("hungergames.newarena")){

            if (isset($params[0]) && isset($params[1])) {
                $this->arenaManager->addArena($params[0], $params[1]);
                $sender->sendMessage(TextFormat::DARK_BLUE . "[HungerGames]" . TextFormat::WHITE . " Arena added");
            }

            else {
                $sender->sendMessage(TextFormat::DARK_BLUE . "[HungerGames]" . TextFormat::WHITE . " Usage: /newarena [id] [capacity]");
            }

            return true;
        }

        else{
            $sender->sendMessage(TextFormat::RED . "You do not have the permissions to run this command");
        }
    }
}