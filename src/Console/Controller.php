<?php

namespace Wearesho\Evrotel\Yii\Console;

use Horat1us\Yii\Exceptions\ModelException;
use Wearesho\Evrotel;
use Wearesho\Yii\Filesystem\Filesystem;
use yii\console;
use yii\db\ActiveRecordInterface;
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
     * @param string|null $date
     * @throws GuzzleHttp\Exception\GuzzleException
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     */
    public function actionCheck(string $date = null): void
    {
        $date = is_string($date) ? Carbon::parse($date) : Carbon::now();
        $billCode = $this->config->getBillCode();

        $response = $this->client->request(
            'GET',
            "https://callme.sipiko.net/statusers/stat_{$billCode}.php",
            [
                GuzzleHttp\RequestOptions::QUERY => [
                    'billcode' => $billCode,
                    'start' => $date->toDateString(),
                ],
            ]
        );

        $body = json_decode((string)$response->getBody(), true);
        $calls = $body['calls'];
        /** @var Evrotel\Yii\Call[] $records */
        $records = [];
        foreach ($calls as $call) {
            $this->stdout($call['uniqueid'] . "\t");
            $record = $this->parse($call);
            $attributes = $record->getAttributes(null, ['id', 'created_at', 'updated_at',]);
            if (Evrotel\Yii\Call::find()->andWhere($attributes)->exists()) {
                $this->stdout("Skip\n", \yii\helpers\Console::FG_YELLOW);
                continue;
            }
            $records[] = ModelException::saveOrThrow($record);
            $this->stdout("Save\n", \yii\helpers\Console::FG_GREEN);
        }
        $this->stdout("Saved " . count($records) . " calls\n");
    }

    /**
     * @param string|null $date
     * @throws GuzzleHttp\Exception\GuzzleException
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     */
    public function actionCheckAuto(string $date = null): void
    {
        $date = is_string($date) ? Carbon::parse($date) : Carbon::now();
        $billCode = $this->config->getBillCode();

        $response = $this->client->request(
            'GET',
            "https://callme.sipiko.net/statusers/stat_{$billCode}_auto.php",
            [
                GuzzleHttp\RequestOptions::QUERY => [
                    'billcode' => $billCode,
                    'start' => $date->toDateString(),
                ],
            ]
        );

        $body = json_decode((string)$response->getBody(), true);
        $calls = $body['calls'];

        /** @var Evrotel\Yii\Call[] $records */
        $records = [];
        foreach ($calls as $call) {
            $this->stdout($call['uniqueid'] . "\t");
            $record = $this->parse($call, true);
            if (Evrotel\Yii\Call::find()->andWhere($record->attributes)->exists()) {
                $this->stdout("Skip\n", \yii\helpers\Console::FG_YELLOW);
                continue;
            }

            /** @noinspection PhpUnhandledExceptionInspection */
            $records[] = Evrotel\Yii\Call::getDb()->transaction(function () use (
                $records,
                $record
            ): ActiveRecordInterface {
                return ModelException::saveOrThrow($record);
            });

            if ($record->task) {
                $this->stdout("Task {$record->task->id}\t", helpers\Console::FG_PURPLE);
            }
            $this->stdout("Save\n", \yii\helpers\Console::FG_GREEN);
        }
        $this->stdout("Saved " . count($records) . " calls\n");
    }

    protected function parse(array $call, bool $isAuto = false): Evrotel\Yii\Call
    {
        $file = 'http://m01.sipiko.net' . str_replace('/var/www', '', $call['recfile']);
        $at = Carbon::parse($call['calldate']);

        $attributes = [
            'from' => $call['numberA'],
            'to' => $call['numberB'],
            'direction' => $call['direction'],
            'disposition' => $call['disposition'],
            'duration' => $call['billsec'],
            'finished' => true,
            'file' => $file,
            'at' => $at->toDateTimeString(),
            'is_auto' => $isAuto,
        ];

        return new Evrotel\Yii\Call($attributes);
    }

    protected function log(int $count): void
    {
        $this->stdout("Created {$count} tasks.", helpers\Console::FG_GREEN);
    }

    protected function createJobs(): int
    {
        $tasks = Evrotel\Yii\Task::find()
            ->withoutJobs()
            ->andAtReached()
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
