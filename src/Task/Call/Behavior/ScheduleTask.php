<?php

namespace Wearesho\Evrotel\Yii\Task\Call\Behavior;

use yii\base;
use yii\db;
use Wearesho\Evrotel;
use Carbon\Carbon;

/**
 * Class ScheduleTask
 * @package Wearesho\Evrotel\Yii\Task\Call\Behavior
 */
class ScheduleTask extends Evrotel\Yii\Task\Call\Behavior
{
    public function events(): array
    {
        return [
            db\ActiveRecord::EVENT_AFTER_INSERT => 'scheduleNextTask',
        ];
    }

    /**
     * @param db\AfterSaveEvent $event
     * @throws base\InvalidConfigException
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     */
    public function scheduleNextTask(db\AfterSaveEvent $event): void
    {
        $relation = $this->extractCallRelation($event);
        if (!$relation->task->isRepeatable()) {
            return;
        }
        if ($relation->call->disposition !== Evrotel\Call\Disposition::ANSWERED) {
            return;
        }

        $repeat = $relation->task->repeat;
        if ($relation->call->duration >= $repeat->min_duration) {
            \Yii::debug(
                "Skip scheduling next task after {$relation->evrotel_task_id}: "
                . "duration reached in call {$relation->evrotel_call_id}",
                static::class
            );
            return;
        }

        $nextTask = $relation->task->repeat();
        \Yii::info("Created task {$nextTask->id} after {$relation->evrotel_task_id}", static::class);
    }
}
