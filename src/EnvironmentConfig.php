<?php

declare(strict_types=1);

namespace Wearesho\Evrotel\Yii;

use Wearesho\Evrotel;

/**
 * Class EnvironmentConfig
 * @package Wearesho\Evrotel\Yii
 */
class EnvironmentConfig extends Evrotel\EnvironmentConfig implements ConfigInterface
{
    public function getChannels(): int
    {
        return (int)$this->getEnv('CHANNELS', ConfigInterface::DEFAULT_CHANNELS);
    }

    public function getJobInterval(): int
    {
        return (int)$this->getEnv('JOB_INTERVAL', ConfigInterface::DEFAULT_JOB_INTERVAL);
    }
}
