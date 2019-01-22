<?php

namespace Wearesho\Evrotel\Yii\Console\Job;

use Carbon\Carbon;
use Horat1us\Yii\Exceptions\ModelException;
use Wearesho\Evrotel;
use yii\di;
use yii\base;
use yii\queue;

/**
 * Class DialJob
 * @package Wearesho\Evrotel\Yii\Console
 */
class Dial extends Evrotel\Yii\Console\Job
{
    /** @var string|array|Evrotel\AutoDial\Worker */
    public $worker = [
        'class' => Evrotel\AutoDial\Worker::class,
    ];

    /**
     * @param queue\Queue $queue which pushed and is handling the job
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws base\InvalidConfigException
     * @throws Evrotel\AutoDial\Exception
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     */
    public function execute($queue)
    {
        /** @var Evrotel\AutoDial\Worker $worker */
        $worker = di\Instance::ensure($this->worker, Evrotel\AutoDial\Worker::class);
        $task = $this->getTask();
        $request = $this->getRequest($task);

        try {
            $disposition = $worker->push($request);
        } catch (Evrotel\AutoDial\Exception $exception) {
            $task->status = Evrotel\Yii\Task::STATUS_ERROR;
            $task->response = (string)$exception->getResponse()->getBody();
            ModelException::saveOrThrow($task);

            $task->copy(Carbon::now()->addMinute());

            throw $exception;
        }

        if ($disposition !== Evrotel\Call\Disposition::ANSWERED && $task->isRepeatable()) {
            $task->repeat();
        }

        $task->status = Evrotel\Yii\Task::STATUS_CLOSED;
        $task->response = $disposition;
        ModelException::saveOrThrow($task);

        \Yii::info("Task {$this->taskId} disposition: " . $disposition, static::class);
    }
}
