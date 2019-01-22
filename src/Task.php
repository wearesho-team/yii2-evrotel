<?php

namespace Wearesho\Evrotel\Yii;

use Carbon\Carbon;
use Horat1us\Yii\Exceptions\ModelException;
use Horat1us\Yii\Validators\ConstRangeValidator;
use Wearesho\Evrotel;
use yii\behaviors\TimestampBehavior;
use yii\db;

/**
 * Class Task
 * @package Wearesho\Evrotel\Yii
 *
 * @property string $id [integer]
 * @property string $queue_id [integer]
 * @property string $recipient [varchar(12)]
 * @property string $file
 * @property int $created_at [timestamp(0)]
 * @property int $updated_at [timestamp(0)]
 * @property int $previous_id [integer]  Previous Repeat Task
 * @property int $at [timestamp(0)]  Queue Job will not be created before this timestamp
 * @property string $status [varchar(7)]
 * @property string $response Response from autodial server
 *
 * @property Task $previous
 * @property-read Task $next
 * @property-read Task\Repeat $repeat
 * @property-read Call $call
 * @property-read int $number
 */
class Task extends db\ActiveRecord
{
    public const STATUS_WAITING = 'waiting'; // Job is not sent to Evrotel
    public const STATUS_PROCESS = 'process'; // Job is sent to Evrotel, waiting for result
    public const STATUS_CLOSED = 'closed'; // Job status fetched
    public const STATUS_ERROR = 'error'; // Pushing request failed

    public static function tableName(): string
    {
        return 'evrotel_task';
    }

    public static function find(): Task\Query
    {
        return new Task\Query;
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
            'status' => [
                'class' => Task\Behavior\Status::class,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['file', 'recipient',], 'required',],
            [['queue_id',], 'integer', 'min' => 1,],
            [['recipient',], 'match', 'pattern' => '/^380\d{9}$/',],
            [['file',], 'string',],
            [['previous_id',], 'exist', 'targetRelation' => 'previous',],
            [['at',], 'date', 'format' => 'php:Y-m-d H:i:s',],
            [['status',], 'default', 'value' => static::STATUS_WAITING,],
            [['status',], ConstRangeValidator::class,],
            [['response',], 'string',],
        ];
    }

    public function getPrevious(): db\ActiveQuery
    {
        return $this->hasOne(static::class, ['id' => 'previous_id']);
    }

    public function setPrevious(Task $task): Task
    {
        $this->previous_id = $task->id;
        $this->populateRelation('previous', $task);
        return $this;
    }

    public function getNext(): db\ActiveQuery
    {
        return $this->hasOne(static::class, ['previous_id' => 'id']);
    }

    public function getRepeat(): db\ActiveQuery
    {
        return $this->hasOne(Task\Repeat::class, ['evrotel_task_id' => 'id',]);
    }

    public function getCall(): db\ActiveQuery
    {
        return $this->hasOne(Call::class, ['id' => 'evrotel_call_id'])
            ->viaTable('evrotel_task_call', ['evrotel_task_id' => 'id']);
    }

    /**
     * @return int
     * @throws db\Exception
     */
    public function getNumber(): int
    {
        if (empty($this->id)) {
            throw new \BadMethodCallException(
                "Number can be counted only for saved records"
            );
        }

        $number = static::getDb()
            ->createCommand(/** @lang PostgreSQL */
                <<<QUERY
with recursive cte (id, name, previous_id) as (
  select
    id,
    recipient,
    previous_id
  from       evrotel_task
  where      id = :id
  union all
  select
    p.id,
    p.recipient,
    p.previous_id
  from       evrotel_task p
    inner join cte
      on p.id = cte.previous_id
)
select count(id) from cte;
QUERY
                ,
                [
                    'id' => $this->id
                ]
            )
            ->queryScalar();

        return $number;
    }

    /**
     * @param \DateTimeInterface|null $at
     * @return Task
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     */
    public function copy(\DateTimeInterface $at = null): Task
    {
        $attributes = $this->getAttributes(['recipient', 'file',]);

        $task = new Task($attributes);
        $task->status = static::STATUS_WAITING;
        if (!is_null($at)) {
            $task->at = $at->format('Y-m-d H:i:s');
        }

        $task->setPrevious($this);

        ModelException::saveOrThrow($task);

        if (!$this->repeat instanceof Task\Repeat) {
            return $task;
        }

        $this->repeat->copy($task);

        return $task;
    }

    public function isRepeatable(): bool
    {
        if (!$this->repeat instanceof Task\Repeat) {
            return false;
        }

        if ($this->number >= $this->repeat->max_count) {
            return false;
        }

        if (Carbon::parse($this->repeat->end_at) < Carbon::now()) {
            return false;
        }

        return true;
    }

    /**
     * @return Task
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     */
    public function repeat(): Task
    {
        if (!$this->isRepeatable()) {
            throw new \BadMethodCallException("Cannot repeat not-repeatable task #{$this->id}");
        }

        return $this->copy(Carbon::now()->addMinutes($this->repeat->interval));
    }
}
