<?php


namespace Wearesho\Evrotel\Yii\Console\Action;

use Horat1us\Yii\Exceptions\ModelException;
use Wearesho\Yii\Filesystem\Filesystem;
use Wearesho\Evrotel;
use yii\base;
use yii\di;
use yii\queue;
use yii\helpers;

/**
 * Class CreateJobs
 * @package Wearesho\Evrotel\Yii\Console\Action
 */
class CreateJobs extends base\Action
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

    /**
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     */
    public function run(): void
    {
        $tasks = Evrotel\Yii\Task::find()
            ->withoutJobs()
            ->andWhere(['=', 'evrotel_task.status', Evrotel\Yii\Task::STATUS_WAITING])
            ->andAtReached()
            ->orderBy(['id' => SORT_DESC])
            ->limit($this->getAvailableChannelsCount())
            ->all();

        foreach ($tasks as $task) {
            $publicFilePath = $this->fs->getUrl($task->file);
            $request = new Evrotel\AutoDial\Request($task->recipient, $publicFilePath);

            $id = $this->queue->push(new Evrotel\Yii\Console\Job([
                'request' => $request,
            ]));

            $task->queue_id = $id;

            /** @noinspection PhpUnhandledExceptionInspection */
            ModelException::saveOrThrow($task, ['queue_id']);
        }
    }

    protected function log(int $count): void
    {
        $this->controller->stdout("Created {$count} tasks.", helpers\Console::FG_GREEN);
    }

    protected function getAvailableChannelsCount(): int
    {
        $busyChannelsCount = Evrotel\Yii\Task::find()
            ->andWhere(['=', 'evrotel_task.status', Evrotel\Yii\Task::STATUS_PROCESS])
            ->count();
        $totalChannelsCount = $this->config->getChannels();

        return $totalChannelsCount - $busyChannelsCount;
    }
}
