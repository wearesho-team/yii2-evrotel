<?php

namespace Wearesho\Evrotel\Yii\Task;

use yii\db;
use Wearesho\Evrotel;

/**
 * Class Call
 * @package Wearesho\Evrotel\Yii\Task
 *
 * @property string $evrotel_task_id [integer]
 * @property string $evrotel_call_id [integer]
 *
 * @property Evrotel\Yii\Task $task
 * @property Evrotel\Yii\Call $call
 */
class Call extends db\ActiveRecord
{
    public static function tableName(): string
    {
        return "evrotel_task_call";
    }

    public static function primaryKey(): array
    {
        return ['evrotel_task_id', 'evrotel_call_id',];
    }

    public function behaviors(): array
    {
        return [
            'schedule' => [
                'class' => Call\Behavior\ScheduleTask::class,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['evrotel_call_id', 'evrotel_task_id',], 'required',],
            [['evrotel_call_id',], 'exist', 'targetRelation' => 'call',],
            [['evrotel_task_id',], 'exist', 'targetRelation' => 'task',],
        ];
    }

    public function getTask(): db\ActiveQuery
    {
        return $this->hasOne(Evrotel\Yii\Task::class, ['id' => 'evrotel_task_id']);
    }

    public function setTask(Evrotel\Yii\Task $task): Call
    {
        $this->evrotel_task_id = $task->id;
        $this->populateRelation('task', $task);
        return $this;
    }

    public function getCall(): db\ActiveQuery
    {
        return $this->hasOne(Evrotel\Yii\Call::class, ['id' => 'evrotel_call_id']);
    }

    public function setCall(Evrotel\Yii\Call $call): Call
    {
        $this->evrotel_call_id = $call->id;
        $this->populateRelation('call', $call);
        return $this;
    }
}
