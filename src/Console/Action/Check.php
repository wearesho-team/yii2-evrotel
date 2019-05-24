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
        $date = is_string($date) ? Carbon::parse($date)->startOfDay() : Carbon::today();
        $calls = $this->client->getCalls((bool)$this->isAuto, $date);


        $ids = array_map(function (Evrotel\Statistics\Call $call): int {
            return $call->getId();
        }, $calls->getArrayCopy());

        /** @noinspection PhpUnhandledExceptionInspection */
        $storedCalls = Evrotel\Yii\Call::find()
            ->andWhere(['in', 'external_id', $ids])
            ->select(['external_id', 'is_auto'])
            ->createCommand()
            ->queryAll(\PDO::FETCH_KEY_PAIR);

        /** @var Evrotel\Yii\Call[] $records */
        $records = [];
        /** @var Evrotel\Statistics\Call $call */
        foreach ($calls as $call) {
            $id = $call->getId();
            $this->controller->stdout($id . "\t");

            if (array_key_exists($id, $storedCalls)) {
                if ($storedCalls[$id] === $call->isAuto()) {
                    $this->controller->stdout("Skip\n", Console::FG_YELLOW);
                    continue;
                }

                $record = Evrotel\Yii\Call::find()
                    ->andWhere(['=', 'external_id', $id])
                    ->one();
                $record->is_auto = $call->isAuto();
            } else {
                $record = Evrotel\Yii\Call::from($call);
            }

            try {
                /** @noinspection PhpUnhandledExceptionInspection */
                Evrotel\Yii\Call::getDb()->transaction(function () use (
                    $record
                ): void {
                    ModelException::saveOrThrow($record);
                });
            } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ModelExceptionInterface $exception) {
                if (count($exception->getModel()->getFirstErrors()) === 1
                    && $exception->getModel()->getFirstError('external_id')
                ) {
                    $this->controller->stdout("Duplicate\n", Console::FG_YELLOW);
                    continue;
                }

                $this->controller->stdout($exception->getMessage() . "\n", Console::FG_RED);
                return;
            }

            $records[] = $record;
            $this->controller->stdout(
                $record->is_auto ? "AUTO" : "MANUAL",
                $record->is_auto ? Console::FG_YELLOW : Console::FG_CYAN
            );
            $this->controller->stdout(" Save\n", Console::FG_GREEN);
        }
        $this->controller->stdout("Saved " . count($records) . " calls\n");
    }
}
