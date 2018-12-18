<?php

namespace Wearesho\Evrotel\Yii\Call;

use yii\base;
use Wearesho\Evrotel;

/**
 * Class Behavior
 * @package Wearesho\Evrotel\Yii\Call
 */
abstract class Behavior extends base\Behavior
{
    /**
     * @param base\Event $event
     * @return Evrotel\Yii\Call
     * @throws base\InvalidConfigException
     */
    protected function extractCall(base\Event $event): Evrotel\Yii\Call
    {
        if (!$event->sender instanceof Evrotel\Yii\Call) {
            throw new base\InvalidConfigException(
                static::class
                . " can be only appended to "
                . Evrotel\Yii\Call::class
            );
        }

        return $event->sender;
    }
}
