<?php

namespace SemyaChecksExporter\Data;

class DetailsInfo
{
    /**
     * @var string
     */
    public $purchaseId;
    /**
     * @var int
     */
    public $count = 10;
    /**
     * @var string
     */
    public $deviceId;
    /**
     * @var string
     */
    public $cardId;
    /**
     * @var string
     */
    public $token;

    public static function createFromConfig(Config $config) : self
    {
        $info = new self;
        $info->token = $config->token;
        $info->deviceId = $config->deviceId;
        $info->cardId = $config->canonicalCardId;

        return $info;
    }

    public function setPurchaseId(string $purchaseId) : self
    {
        $this->purchaseId = $purchaseId;

        return $this;
    }
}
