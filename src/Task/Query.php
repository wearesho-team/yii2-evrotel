<?php

namespace Wearesho\Evrotel\Yii\Task;

use Carbon\Carbon;
use Wearesho\Evrotel;
use yii\db;

/**
 * Class Query
 * @package Wearesho\Evrotel\Yii\Task
 */
class Query extends db\ActiveQuery
{
    public function __construct(string $modelClass = Evrotel\Yii\Task::class, array $config = [])
    {
        parent::__construct($modelClass, $config);
    }

    public function withoutJobs(): Query
    {
        return $this->andWhere('evrotel_task.queue_id is null');
    }

    public function andAtReached(): Query
    {
        return $this->andWhere([
            'or',
            ['is', 'evrotel_task.at', null],
            ['<=', 'evrotel_task.at', Carbon::now()->toDateTimeString(),]
        ]);
    }
}
