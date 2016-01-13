<?php

namespace HungerGames\Command;

use HungerGames\ArenaManager;
use HungerGames\HungerGames;
use pocketmine\command\Command;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

/**
 * Command to add a new chest to the arenas
 * Class ChestPlaceCommand
 * @package HungerGames\Command
 */
class ChestPlaceCommand extends Command implements PluginIdentifiableCommand {

    private $plugin;
    private $arenaManager;
    public $commandName = "setchest";
    public $CommandIssuer;

    public function __construct(HungerGames $plugin, ArenaManager $arenaManager) {
        parent::__construct($this->commandName, "Set a resettable chest");
        $this->setUsage("/$this->commandName" . " [arena number]");

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

        if ($sender->hasPermission("hungergames.setchest")) {

            $this->arenaManager->setChestPlacedIssuer($sender);

            return true;
        }

        else {
            $sender->sendMessage(TextFormat::RED . "[HungerGames] You do not have the permissions to use this command");
        }
    }

}