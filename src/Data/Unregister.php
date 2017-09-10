<?php

namespace SemyaChecksExporter\Data;

class Unregister
{
    /**
     * @var string
     */
    public $token;
    /**
     * @var string
     */
    public $cardId;
    /**
     * @var string
     */
    public $deviceId;

    public static function createFromConfig(Config $config) : self
    {
        $result = new self;

        $result->token = $config->token;
        $result->deviceId = $config->deviceId;
        $result->cardId = $config->canonicalCardId;

        return $result;
    }
}