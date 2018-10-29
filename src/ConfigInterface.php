<?php

namespace Wearesho\Evrotel\Yii;

/**
 * Interface ConfigInterface
 * @package Wearesho\Evrotel\Yii
 */
interface ConfigInterface
{
    public const DEFAULT_CHANNELS = 5;
    public const DEFAULT_JOB_INTERVAL = 1;

    public function getChannels(): int;

    /**
     * Time between creating jobs from tasks
     * @return int minutes
     */
    public function getJobInterval(): int;
}
