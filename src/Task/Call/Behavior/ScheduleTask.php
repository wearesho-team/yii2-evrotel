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

        $repeat = $relation->task->repeat;

        if (!$repeat instanceof Evrotel\Yii\Task\Repeat) {
            \Yii::debug(
                "Skip scheduling next task after {$relation->evrotel_task_id}: missing repeat config",
                static::class
            );
            return;
        }

        if (Carbon::parse($repeat->end_at) < Carbon::now()) {
            \Yii::debug(
                "Skip scheduling next task after {$relation->evrotel_task_id}: period limit",
                static::class
            );
            return;
        }

        if ($relation->call->duration >= $repeat->min_duration) {
            \Yii::debug(
                "Skip scheduling next task after {$relation->evrotel_task_id}: "
                . "duration reached in call {$relation->evrotel_call_id}",
                static::class
            );
            return;
        }

        $number = $relation->task->number;
        if ($number >= $repeat->max_count) {
            \Yii::debug(
                "Skip scheduling next task after {$relation->evrotel_task_id}: "
                . "count reached {$number}",
                static::class
            );
            return;
        }

        $nextTask = $relation->task->copy(Carbon::now()->addMinutes($repeat->interval));
        \Yii::info("Created task {$nextTask->id} after {$relation->evrotel_task_id}", static::class);
    }
}
