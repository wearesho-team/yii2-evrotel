<?php

namespace Wearesho\Evrotel\Yii\Task;

use Horat1us\Yii\Exceptions\ModelException;
use yii\db;
use Wearesho\Evrotel;

/**
 * Class Repeat
 * @package Wearesho\Evrotel\Yii\Task
 *
 * @property int $evrotel_task_id [integer]
 * @property int $min_duration [integer]
 * @property int $max_count [integer]
 * @property int $interval [integer]
 * @property int $end_at [timestamp(0)]
 */
class Repeat extends db\ActiveRecord
{
    public static function tableName(): string
    {
        return "evrotel_task_repeat";
    }

    public static function primaryKey(): array
    {
        return ['evrotel_task_id',];
    }

    public function rules(): array
    {
        return [
            [['evrotel_task_id', 'min_duration', 'max_count', 'interval', 'end_at',], 'required',],
            [['evrotel_task_id',], 'exist', 'targetRelation' => 'task',],
            [['min_duration', 'max_count', 'interval',], 'integer', 'min' => 0,],
            [['end_at',], 'date', 'format' => 'php:Y-m-d H:i:s',],
        ];
    }

    public function getTask(): db\ActiveQuery
    {
        return $this->hasOne(Evrotel\Yii\Task::class, ['id' => 'evrotel_task_id']);
    }

    public function setTask(Evrotel\Yii\Task $task): Repeat
    {
        $this->evrotel_task_id = $task->id;
        $this->populateRelation('task', $task);
        return $this;
    }

    /**
     * Creates copy of current repeat configuration for new task
     *
     * @param Evrotel\Yii\Task $task
     * @return Repeat
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     */
    public function copy(Evrotel\Yii\Task $task): Repeat
    {
        $attributes = $this->getAttributes(['min_duration', 'max_count', 'interval', 'end_at',]);

        $repeat = new static($attributes);
        $repeat->setTask($task);

        ModelException::saveOrThrow($repeat);
        $task->populateRelation('repeat', $repeat);

        return $repeat;
    }
}
