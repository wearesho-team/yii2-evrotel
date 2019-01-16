<?php

namespace Wearesho\Evrotel\Yii;

use Carbon\Carbon;
use Wearesho\Evrotel;
use Horat1us\Yii\Validators\ConstRangeValidator;
use yii\behaviors\TimestampBehavior;
use yii\db;

/**
 * Class Call
 * @package Wearesho\Evrotel\Yii
 *
 * @property string $id [integer]
 * @property string $from [varchar(255)]
 * @property string $to [varchar(255)]
 * @property string $direction [evrotel_direction]
 * @property bool $finished [boolean]
 * @property string $disposition [evrotel_disposition]
 * @property string $file [text]
 * @property string $duration [integer]
 * @property int $at [timestamp(0)]
 * @property int $created_at [timestamp(0)]
 * @property int $updated_at [timestamp(0)]
 * @property bool $is_auto [boolean]
 *
 * @property-read Task $task
 */
class Call extends db\ActiveRecord
{
    public static function tableName(): string
    {
        return 'evrotel_call';
    }

    public function behaviors(): array
    {
        return [
            'ts' => [
                'class' => TimestampBehavior::class,
                'value' => function (): string {
                    return Carbon::now()->toDateTimeString();
                },
            ],
            'relate' => [
                'class' => Call\Behavior\RelateToTask::class,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['from', 'to', 'direction', 'at',], 'required',],
            [['from', 'to',], 'string', 'max' => 255,],
            [['finished',], 'default', 'value' => false,],
            [['finished',], 'boolean',],
            [
                ['direction',],
                ConstRangeValidator::class,
                'targetClass' => Evrotel\Call\Direction::class,
                'prefix' => '',
            ],
            [
                ['disposition',],
                ConstRangeValidator::class,
                'targetClass' => Evrotel\Call\Disposition::class,
                'prefix' => '',
            ],
            [['at',], 'date', 'format' => 'php:Y-m-d H:i:s',],
            [['file',], 'string',],
            [['duration',], 'integer',],
            [['is_auto',], 'boolean',],
            [['is_auto',], 'default', 'value' => false,],
        ];
    }

    public function getTask(): db\ActiveQuery
    {
        return $this->hasOne(Task::class, ['id' => 'evrotel_task_id'])
            ->viaTable('evrotel_task_call', ['evrotel_call_id' => 'id']);
    }

    public function isDuplicate(): bool
    {
        $except = ['id', 'created_at', 'updated_at',];
        if (!$this->is_auto) {
            // not auto calls may duplicate auto calls
            $except[] = 'is_auto';
        }
        $attributes = $this->getAttributes(null, $except);
        return static::find()->andWhere($attributes)->exists();
    }

    /**
     * For auto calls may be created same call with is_auto=false
     * @return self
     */
    public function getNotAutoClone(): ?self
    {
        if (!$this->is_auto) {
            throw new \BadMethodCallException(__METHOD__ . ' can be called only for auto call records');
        }

        $attributes = $this->getAttributes(null, ['id', 'created_at', 'updated_at', 'is_auto']);
        return static::find()->andWhere($attributes)->one();
    }

    public function findRelatedTask(): ?Task
    {
        if (!$this->is_auto) {
            throw new \BadMethodCallException(
                "Can not find task for not-auto call"
            );
        }

        if ($this->task instanceof Task) {
            return $this->task;
        }

        $task = Task::find()
            ->joinWith('call')
            ->andWhere('evrotel_call.id is null')
            ->andWhere('evrotel_task.queue_id is not null')
            ->andWhere(['=', 'evrotel_task.recipient', $this->to])
            ->andWhere(
                new db\Expression(
                    '(:date - coalesce(evrotel_task.at, evrotel_task.updated_at)) < interval \'10\' minute'
                ),
                [
                    'date' => $this->at,
                ]
            )
            ->orderBy(['evrotel_task.id' => SORT_ASC])
            ->one();

        return $task;
    }

    public static function from(Evrotel\Statistics\Call $statistics): Call
    {
        return new static([
            'from' => $statistics->getFrom(),
            'to' => $statistics->getTo(),
            'direction' => $statistics->getDirection(),
            'disposition' => $statistics->getDisposition(),
            'duration' => $statistics->getDuration(),
            'finished' => true,
            'file' => $statistics->getFile(),
            'at' => $statistics->getDate()->format('Y-m-d H:i:s'),
            'is_auto' => $statistics->isAuto(),
        ]);
    }
}
