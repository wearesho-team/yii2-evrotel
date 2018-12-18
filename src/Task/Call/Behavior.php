<?php

namespace Wearesho\Evrotel\Yii\Task\Call;

use yii\base;
use Wearesho\Evrotel;

/**
 * Class Behavior
 * @package Wearesho\Evrotel\Yii\Task\Call
 */
abstract class Behavior extends base\Behavior
{
    /**
     * @param base\Event $event
     * @return Evrotel\Yii\Task\Call
     * @throws base\InvalidConfigException
     */
    protected function extractCallRelation(base\Event $event): Evrotel\Yii\Task\Call
    {
        if (!$event->sender instanceof Evrotel\Yii\Task\Call) {
            throw new base\InvalidConfigException(
                static::class
                . " can be only appended to "
                . Evrotel\Yii\Task\Call::class
            );
        }

        return $event->sender;
    }
}
