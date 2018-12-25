<?php

declare(strict_types=1);

namespace Wearesho\Evrotel\Yii\Console\Action;

use Carbon\Carbon;
use Horat1us\Yii\Exceptions\ModelException;
use Wearesho\Evrotel;
use yii\db;
use yii\di;
use yii\base;

/**
 * Class Check
 * @package Wearesho\Evrotel\Yii\Console\Action
 */
class Check extends base\Action
{
    /**
     * If true, checks only auto calls
     * If false, checks only manual calls
     * @var bool
     */
    public $isAuto = false;

    /** @var array|string|Evrotel\Statistics\Client */
    public $client = [
        'class' => Evrotel\Statistics\Client::class,
    ];

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        $this->client = di\Instance::ensure($this->client, Evrotel\Statistics\Client::class);
    }

    /**
     * @param string|null $date
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     */
    public function run(string $date = null)
    {
        $date = is_string($date) ? Carbon::parse($date) : Carbon::now();
        $calls = $this->client->getCalls((bool)$this->isAuto);
        /** @var Evrotel\Yii\Call[] $records */
        $records = [];
        /** @var Evrotel\Statistics\Call $call */
        foreach ($calls as $call) {

            $this->controller->stdout($call->getId() . "\t");
            $record = Evrotel\Yii\Call::from($call);
            if ($record->isDuplicate()) {
                $this->controller->stdout("Skip\n", \yii\helpers\Console::FG_YELLOW);
                continue;
            }
            /** @noinspection PhpUnhandledExceptionInspection */
            Evrotel\Yii\Call::getDb()->transaction(function () use (
                $records,
                $record
            ): db\ActiveRecordInterface {
                return ModelException::saveOrThrow($record);
            });

            $records[] = ModelException::saveOrThrow($record);
            $this->controller->stdout("Save\n", \yii\helpers\Console::FG_GREEN);
        }
        $this->controller->stdout("Saved " . count($records) . " calls\n");
    }
}
