<?php

declare(strict_types=1);

namespace Wearesho\Evrotel\Yii\Console\Action;

use Carbon\Carbon;
use Horat1us\Yii\Exceptions\ModelException;
use Horat1us\Yii\Interfaces\ModelExceptionInterface;
use Wearesho\Evrotel;
use yii\di;
use yii\base;
use yii\helpers\Console;

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
     */
    public function run(string $date = null)
    {
        $date = is_string($date) ? Carbon::parse($date) : Carbon::now();
        $calls = $this->client->getCalls((bool)$this->isAuto, $date);
        /** @var Evrotel\Yii\Call[] $records */
        $records = [];
        /** @var Evrotel\Statistics\Call $call */
        foreach ($calls as $call) {
            $this->controller->stdout($call->getId() . "\t");
            $record = Evrotel\Yii\Call::from($call);
            if ($record->isDuplicate()) {
                $this->controller->stdout("Skip\n", Console::FG_YELLOW);
                continue;
            }
            try {
                /** @noinspection PhpUnhandledExceptionInspection */
                Evrotel\Yii\Call::getDb()->transaction(function () use (
                    $record
                ): void {
                    ModelException::saveOrThrow($record);
                });
            } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ModelExceptionInterface $exception) {
                $this->controller->stdout($exception->getMessage() . "\n", Console::FG_RED);
                return;
            }

            $records[] = $record;
            $this->controller->stdout("Save\n", Console::FG_GREEN);
        }
        $this->controller->stdout("Saved " . count($records) . " calls\n");
    }
}