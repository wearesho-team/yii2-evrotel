<?php

namespace Wearesho\Evrotel\Yii\Task\Call\Behavior;

use Horat1us\Yii\Exceptions\ModelException;
use Wearesho\Evrotel;
use yii\base;
use yii\db;

/**
 * Class Status
 * @package Wearesho\Evrotel\Yii\Task\Call\Behavior
 */
class Status extends Evrotel\Yii\Task\Call\Behavior
{
    public function events(): array
    {
        return [
            db\ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
        ];
    }

    /**
     * Will change task status to `closed` after relating to call
     *
     * @param db\AfterSaveEvent $event
     *
     * @throws base\InvalidConfigException
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     */
    public function afterInsert(db\AfterSaveEvent $event): void
    {
        $relation = $this->extractCallRelation($event);
        if ($relation->task->status !== Evrotel\Yii\Task::STATUS_PROCESS) {
            return;
        }

        $relation->task->status = Evrotel\Yii\Task::STATUS_CLOSED;
        ModelException::saveOrThrow($relation->task);
    }
}
