<?php

declare(strict_types=1);

namespace Wearesho\Evrotel\Yii\Console\Action;

use Wearesho\Evrotel;
use Carbon\Carbon;
use yii\console;
use yii\helpers;
use yii\base;

/**
 * Class Flush
 * @package Wearesho\Evrotel\Yii\Console\Action
 *
 * @property console\Controller $controller
 */
class Flush extends base\Action
{
    public function run(): void
    {
        $number = Evrotel\Yii\Task::updateAll([
            'status' => Evrotel\Yii\Task::STATUS_ERROR,
            'updated_at' => Carbon::now()->toDateTimeString(),
        ], [
            'and',
            ['=', 'status', Evrotel\Yii\Task::STATUS_PROCESS,],
            ['<', 'created_at', Carbon::now()->subMinutes(90)->toDateTimeString(),]
        ]);

        $this->controller->stdout("Removed {$number} processing tasks." . PHP_EOL, helpers\Console::FG_GREEN);
    }
}
