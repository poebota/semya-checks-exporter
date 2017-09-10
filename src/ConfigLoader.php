<?php

namespace SemyaChecksExporter;

use SemyaChecksExporter\Data\Config;

class ConfigLoader
{
    public function load(string $configFileName) : Config
    {
        if (strpos($configFileName, '~') !== false) {
            $info = posix_getpwuid(posix_getuid());
            $configFileName = str_replace('~', $info['dir'], $configFileName);
        }

        $iniConfig = @parse_ini_file($configFileName);

        if (!$iniConfig) {
            throw new \InvalidArgumentException("{$configFileName} was not found or invalid ini-format");
        }

        if (!isset(
             $iniConfig['card_id'],
             $iniConfig['name'],
             $iniConfig['secret']
         )) {
             throw new \InvalidArgumentException("'card_id', 'name', 'secret' options are missing or empty");
        }

        $config = new Config;
        $config->cardId = $iniConfig['card_id'];
        $config->name = $iniConfig['name'];
        $config->secret = $iniConfig['secret'];
        $config->udid = $iniConfig['udid'] ?? null;

        return $config;
    }
}