<?php

namespace SemyaChecksExporter\Data;

class Login
{
    /**
     * @var string
     */
    public $platform = 'android';
    /**
     * @var string
     */
    public $token;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $udid;
    /**
     * @var string
     */
    public $cardId;

    public static function createFromConfig(Config $config) : self
    {
        $login = new self;
        $login->cardId = $config->cardId;
        $login->token = $config->token;
        $login->name = $config->name;
        $login->udid = $config->udid;

        return $login;
    }
}