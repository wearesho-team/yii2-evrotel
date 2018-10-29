<?php

namespace Wearesho\Evrotel\Yii\Task;

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
}
