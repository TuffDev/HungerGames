<?php

namespace HungerGames\Command;

use HungerGames\ArenaManager;
use pocketmine\command\Command;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\command\CommandSender;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

use HungerGames\HungerGames;

class SpawnAddCommand extends Command implements PluginIdentifiableCommand{

    private $plugin;
    public $commandName = "arenaspawn";
    public $commandIssuer;

    public function __construct(HungerGames $plugin, ArenaManager $arenaManager) {
        parent::__construct($this->commandName, "Add a spawn to an arena");
        $this->setUsage("/$this->commandName" . " [arena ID]");

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

        if ($sender->hasPermission("hungergames.spawnadd")) {

            //Get location of issuer
            $position = $sender->getPosition();
            //Add spawnpoint where user is standing for Arena(id)
            if (isset($params[0])) {
                $this->arenaManager->addSpawnpoint($position, $sender, $params[0]);
            }

            else {
                $sender->sendMessage(TextFormat::DARK_BLUE . "[HungerGames]" . TextFormat::WHITE . " Include the id of the arena ");
            }

            return true;
        }

        else {
            $sender->sendMessage(TextFormat::RED . "[HungerGames] You do not have the permissions to use this command");
        }
    }
}