<?php

declare(strict_types=1);

namespace Wearesho\Evrotel\Yii;

use Horat1us\Environment;

/**
 * Class EnvironmentConfig
 * @package Wearesho\Evrotel\Yii
 */
class EnvironmentConfig extends Environment\Yii2\Config implements ConfigInterface
{
    /** @var string */
    public $keyPrefix = 'EVROTEL_';

    public function getChannels(): int
    {
        return (int)$this->getEnv('CHANNELS', ConfigInterface::DEFAULT_CHANNELS);
    }

    public function getJobInterval(): int
    {
        return (int)$this->getEnv('JOB_INTERVAL', ConfigInterface::DEFAULT_JOB_INTERVAL);
    }
}
