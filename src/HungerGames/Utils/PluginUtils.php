<?php

namespace HungerGames\Utils;

use pocketmine\Server;
use pocketmine\utils\TextFormat;

/**
 * Class PluginUtils
 * Basic utility functions for the plugin
 * @package HungerGames\Utils
 */

class PluginUtils {

    public static function consoleLog($message) {
        $logger = Server::getInstance()->getLogger();
        $logger->info(TextFormat::DARK_BLUE . "[HungerGames] " . TextFormat::WHITE . $message);
    }
}