<?php

namespace Wearesho\Evrotel\Yii;

use Carbon\Carbon;
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
 */
class Task extends db\ActiveRecord
{
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
        ];
    }

    public function rules(): array
    {
        return [
            [['file', 'recipient',], 'required',],
            [['queue_id',], 'integer', 'min' => 1,],
            [['recipient',], 'match', 'pattern' => '/^380\d{9}$/',],
            [['file',], 'string',],
        ];
    }
}
