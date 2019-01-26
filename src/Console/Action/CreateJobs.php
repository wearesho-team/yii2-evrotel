<?php


namespace Wearesho\Evrotel\Yii\Console\Action;

use Carbon\Carbon;
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
     * @throws base\InvalidConfigException
     */
    public function run(): void
    {
        $tasks = Evrotel\Yii\Task::find()
            ->withoutJobs()
            ->andWhere(['=', 'evrotel_task.status', Evrotel\Yii\Task::STATUS_WAITING])
            ->andWhere(['>', 'evrotel_task.created_at', Carbon::now()->toDateString()])
            ->andAtReached()
            ->orderBy(['id' => SORT_ASC])
            ->limit($this->getAvailableChannelsCount())
            ->all();

        $timeouts = [];

        foreach ($tasks as $task) {
            if (array_key_exists($task->file, $timeouts)) {
                $this->queue->delay($timeouts[$task->file]);
            }

            $job = new Evrotel\Yii\Console\Job\Media([
                'taskId' => $task->id,
            ]);
            if ($job->isPushed()) {
                $job = new Evrotel\Yii\Console\Job\Dial([
                    'taskId' => $task->id,
                ]);
            }
            $id = $this->queue->push($job);

            $task->queue_id = $id;

            /** @noinspection PhpUnhandledExceptionInspection */
            ModelException::saveOrThrow($task);

            $timeouts[$task->file] = 10;

            $this->controller->stdout(
                "Task #{$task->id} pushed to queue as " . get_class($job) . "#{$id}",
                helpers\Console::FG_GREEN
            );
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
