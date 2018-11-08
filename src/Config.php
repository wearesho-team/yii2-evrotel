<?php

declare(strict_types=1);

namespace Wearesho\Evrotel\Yii;

use Wearesho\Evrotel;
use yii\base;

/**
 * Class Config
 * @package Wearesho\Evrotel\Yii
 */
class Config extends base\BaseObject implements ConfigInterface
{
    use Evrotel\ConfigTrait;

    /** @var int */
    public $channels = ConfigInterface::DEFAULT_CHANNELS;

    public $jobInterval = ConfigInterface::DEFAULT_JOB_INTERVAL;

    public function getChannels(): int
    {
        return (int)$this->channels;
    }

    public function getJobInterval(): int
    {
        return $this->jobInterval;
    }
}
