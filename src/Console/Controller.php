<?php

namespace Wearesho\Evrotel\Yii\Console;

use Horat1us\Yii\Exceptions\ModelException;
use Wearesho\Evrotel;
use Wearesho\Yii\Filesystem\Filesystem;
use yii\console;
use yii\helpers;
use yii\di;
use yii\queue;

/**
 * Class Controller
 * @package Wearesho\Evrotel\Yii\Console
 */
class Controller extends console\Controller
{
    /** @var array|string|Evrotel\Yii\ConfigInterface */
    public $config = [
        'class' => Evrotel\Yii\ConfigInterface::class,
    ];

    /** @var array|string|queue\Queue */
    public $queue = 'queue';

    /** @var array|string|Filesystem */
    public $fs = [
        'class' => Filesystem::class,
    ];

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        $this->config = di\Instance::ensure($this->config, Evrotel\Yii\ConfigInterface::class);
        $this->queue = di\Instance::ensure($this->queue, queue\Queue::class);
        $this->fs = di\Instance::ensure($this->fs, Filesystem::class);
    }

    public function actionRun(): void
    {
        $count = $this->createJobs();
        $this->log($count);
    }

    public function actionListen(): void
    {
        while (true) {
            $count = $this->createJobs();
            if ($count > 0) {
                $this->log($count);
            }

            sleep((int)$this->config->getJobInterval() * 60);
        }
    }

    protected function log(int $count): void
    {
        $this->stdout("Created {$count} tasks.", helpers\Console::FG_GREEN);
    }

    protected function createJobs(): int
    {
        $tasks = Evrotel\Yii\Task::find()
            ->withoutJobs()
            ->orderBy(['id' => SORT_ASC])
            ->limit($this->config->getChannels())
            ->all();

        foreach ($tasks as $task) {
            $publicFilePath = $this->fs->getUrl($task->file);
            $request = new Evrotel\AutoDial\Request($task->recipient, $publicFilePath);

            $id = $this->queue->push(new Job([
                'request' => $request,
            ]));

            $task->queue_id = $id;

            /** @noinspection PhpUnhandledExceptionInspection */
            ModelException::saveOrThrow($task, ['queue_id']);
        }

        return count($tasks);
    }
}
