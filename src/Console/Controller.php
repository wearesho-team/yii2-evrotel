<?php

namespace Wearesho\Evrotel\Yii\Console;

use Horat1us\Yii\Exceptions\ModelException;
use Wearesho\Evrotel;
use Wearesho\Yii\Filesystem\Filesystem;
use yii\console;
use yii\helpers;
use yii\di;
use yii\queue;
use Carbon\Carbon;
use GuzzleHttp;

/**
 * Class Controller
 * @package Wearesho\Evrotel\Yii\Console
 */
class Controller extends console\Controller
{
    /** @var array|string|GuzzleHttp\ClientInterface */
    public $client = [
        'class' => GuzzleHttp\ClientInterface::class,
    ];

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
        $this->client = di\Instance::ensure($this->client, GuzzleHttp\ClientInterface::class);
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

    /**
     * @throws GuzzleHttp\Exception\GuzzleException
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     */
    public function actionCheck(): void
    {
        $billCode = $this->config->getBillCode();

        $response = $this->client->request(
            'GET',
            "https://callme.sipiko.net/statusers/stat_{$billCode}.php",
            [
                GuzzleHttp\RequestOptions::QUERY => [
                    'billcode' => $billCode,
                ],
            ]
        );

        $body = json_decode((string)$response->getBody(), true);
        $calls = $body['calls'];
        /** @var Evrotel\Yii\Call[] $records */
        $records = [];
        foreach ($calls as $call) {
            $this->stdout($call['uniqueid'] . "\t");
            $file = 'http://m01.sipiko.net' . str_replace('/var/www', '', $call['recfile']);
            $at = Carbon::parse($call['calldate']);

            $attributes = [
                'from' => $call['numberA'],
                'to' => $call['numberB'],
                'direction' => $call['direction'],
                'disposition' => $call['disposition'],
                'finished' => true,
                'file' => $file,
                'at' => $at->toDateTimeString(),
            ];
            $record = new Evrotel\Yii\Call($attributes);
            if (Evrotel\Yii\Call::find()->andWhere($attributes)->exists()) {
                $this->stdout("Skip\n", \yii\helpers\Console::FG_YELLOW);
                continue;
            }
            $records[] = ModelException::saveOrThrow($record);
            $this->stdout("Save\n", \yii\helpers\Console::FG_GREEN);
        }
        $this->stdout("Saved " . count($records) . " calls\n");
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
