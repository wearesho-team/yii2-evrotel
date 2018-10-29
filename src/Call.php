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
        ];
    }
}
