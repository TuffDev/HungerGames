<?php
namespace HungerGames;

use HungerGames\Model\MatchEndTask;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

use HungerGames\Utils\PluginUtils;
use HungerGames\ArenaManager;
use HungerGames\EventManager;
use HungerGames\Model\MatchStarter;
use HungerGames\Model\ChestRefreshTask;
use HungerGames\Command\AddArenaCommand;
use HungerGames\Command\ChestPlaceCommand;
use HungerGames\Command\SpawnAddCommand;
use HungerGames\Command\SpawnToArenaCommand;
use HungerGames\Command\AddLobbyCommand;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;

class HungerGames extends PluginBase {

    /**
     * @var HungerGames
     */
    private static $instance;

    /**
     * @var ArenaManager
     */
    private $arenaManager;

    /**
     * @var Config
     */
    public $arenaConfig;

    public function onEnable() {
        self::$instance = $this;
        PluginUtils::consoleLog("Initializing");

        // Get arena positions from arenas.yml
        @mkdir($this->getDataFolder());
        $this->arenaConfig = new Config($this->getDataFolder()."config.yml", Config::YAML, array());

        $this->arenaManager = new ArenaManager();
        $this->arenaManager->init($this->arenaConfig);

        //Register events
        $this->getServer()->getPluginManager()->registerEvents(
            new EventManager($this->arenaManager),
            $this
        );

        //Register commands
        $addArenaCommand = new AddArenaCommand($this, $this->arenaManager);
        $this->getServer()->getCommandMap()->register($addArenaCommand->commandName, $addArenaCommand);

        $chestPlaceCmd = new ChestPlaceCommand($this, $this->arenaManager);
        $this->getServer()->getCommandMap()->register($chestPlaceCmd->commandName, $chestPlaceCmd);

        $spawnAddCmd = new SpawnAddCommand($this, $this->arenaManager);
        $this->getServer()->getCommandMap()->register($spawnAddCmd->commandName, $spawnAddCmd);

        $spawnToArenaCmd = new SpawnToArenaCommand($this, $this->arenaManager);
        $this->getServer()->getCommandMap()->register($spawnToArenaCmd->commandName, $spawnToArenaCmd);

        $addLobbyCmd = new AddLobbyCommand($this, $this->arenaManager);
        $this->getServer()->getCommandMap()->register($addLobbyCmd->commandName, $addLobbyCmd);

        $task = new MatchStarter($this->getInstance(), $this->arenaManager);
        Server::getInstance()->getScheduler()->scheduleDelayedRepeatingTask($task, 20, 20);

        $chestTask = new ChestRefreshTask($this->getInstance(), $this->arenaManager);
        Server::getInstance()->getScheduler()->scheduleDelayedRepeatingTask($chestTask, 20 *120, 20*120);
    }

    public static function getInstance(){
        return self::$instance;
    }

    public function onDisable() {

    }
}

