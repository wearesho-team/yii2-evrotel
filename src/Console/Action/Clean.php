<?php

declare(strict_types=1);

namespace Wearesho\Evrotel\Yii\Console\Action;

use yii\base;
use yii\console;
use yii\helpers;
use Wearesho\Evrotel;

/**
 * Class Clean
 * @package Wearesho\Evrotel\Yii\Console\Action
 *
 * @property console\Controller $controller
 */
class Clean extends base\Action
{
    public function run(): void
    {
        $number = Evrotel\Yii\Task::updateAll(['status' => Evrotel\Yii\Task::STATUS_CLOSED], [
            'status' => Evrotel\Yii\Task::STATUS_WAITING
        ]);
        $this->controller->stdout("Removed {$number} tasks" . PHP_EOL, helpers\Console::FG_GREEN);
    }
}
