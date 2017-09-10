<?php

namespace SemyaChecksExporter\Data;

class Info
{
    /**
     * @var string
     */
    public $deviceId;
    /**
     * @var int
     */
    public $fromDate;
    /**
     * @var int
     */
    public $toDate;
    /**
     * @var int
     */
    public $sinceLastId = 0;
    /**
     * @var string
     */
    public $token;
    /**
     * @var string
     */
    public $cardId;
    /**
     * @var int
     */
    public $count;

    public static function createFromConfig(Config $config) : self
    {
        $info = new self;
        $info->token = $config->token;
        $info->deviceId = $config->deviceId;
        $info->cardId = $config->canonicalCardId;

        return $info;
    }

    public function setFromDate(int $fromDate) : self
    {
        $this->fromDate = $fromDate;

        return $this;
    }

    public function setToDate(int $toDate) : self
    {
        $this->toDate = $toDate;

        return $this;
    }

    public function setSinceLastId(int $sinceLastId) : self
    {
        $this->sinceLastId = $sinceLastId;

        return $this;
    }

    public function setCount(int $count) : self
    {
        $this->count = $count;

        return $this;
    }
}