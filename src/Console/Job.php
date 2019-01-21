<?php

namespace Wearesho\Evrotel\Yii\Console;

use Wearesho\Evrotel;
use Wearesho\Yii\Filesystem\Filesystem;
use yii\base;
use yii\queue;
use yii\di;

/**
 * Class Job
 * @package Wearesho\Evrotel\Yii\Console
 */
abstract class Job extends base\BaseObject implements queue\JobInterface
{
    /** @var int */
    public $taskId;

    /** @var array|string */
    public $fs = [
        'class' => Filesystem::class,
    ];

    /**
     * @return Filesystem
     * @throws base\InvalidConfigException
     */
    protected function getFs(): Filesystem
    {
        /** @var Filesystem $fs */
        $fs = di\Instance::ensure($this->fs, Filesystem::class);
        return $fs;
    }

    /**
     * @return Evrotel\Yii\Task
     * @throws base\InvalidConfigException
     */
    protected function getTask(): Evrotel\Yii\Task
    {
        if (is_null($this->taskId)) {
            throw new base\InvalidConfigException("taskId have to be specified");
        }
        $task = Evrotel\Yii\Task::findOne((int)$this->taskId);
        if (!$task instanceof Evrotel\Yii\Task) {
            throw new base\InvalidConfigException("Task {$this->taskId} not found");
        }
        return $task;
    }

    /**
     * @param Evrotel\Yii\Task|null $task
     * @return Evrotel\AutoDial\RequestInterface
     * @throws base\InvalidConfigException
     */
    protected function getRequest(Evrotel\Yii\Task $task = null): Evrotel\AutoDial\RequestInterface
    {
        $task = $task ?? $this->getTask();
        $publicFilePath = $this->getFs()->getUrl($task->file);
        return new Evrotel\AutoDial\Request($task->recipient, $publicFilePath);
    }
}
