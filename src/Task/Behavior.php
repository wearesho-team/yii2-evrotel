<?php

namespace Wearesho\Evrotel\Yii\Task;

use yii\base;
use Wearesho\Evrotel;

/**
 * Class Behavior
 * @package Wearesho\Evrotel\Yii\Task
 */
abstract class Behavior extends base\Behavior
{
    /**
     * @param base\Event $event
     * @return Evrotel\Yii\Task
     * @throws base\InvalidConfigException
     */
    protected function extractTask(base\Event $event): Evrotel\Yii\Task
    {
        if (!$event->sender instanceof Evrotel\Yii\Task) {
            throw new base\InvalidConfigException(
                static::class
                . " can be only appended to "
                . Evrotel\Yii\Call::class
            );
        }

        return $event->sender;
    }
}
