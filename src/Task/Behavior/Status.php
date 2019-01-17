<?php

namespace Wearesho\Evrotel\Yii\Task\Behavior;

use yii\db;
use yii\base;
use Wearesho\Evrotel;

/**
 * Class Status
 * @package Wearesho\Evrotel\Yii\Task\Behavior
 */
class Status extends Evrotel\Yii\Task\Behavior
{
    public function events(): array
    {
        return [
            db\ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate', // will change status to `waiting` if
        ];
    }

    /**
     * Change task status to `process` from `waiting` after pushing to queue
     *
     * @param base\ModelEvent $event
     * @throws base\InvalidConfigException
     */
    public function beforeUpdate(base\ModelEvent $event): void
    {
        $task = $this->extractTask($event);
        if ($task->getOldAttribute('queue_id') !== null
            || is_null($task->queue_id)
            || $task->status !== Evrotel\Yii\Task::STATUS_WAITING
        ) {
            return;
        }

        $task->status = Evrotel\Yii\Task::STATUS_PROCESS;
    }
}
