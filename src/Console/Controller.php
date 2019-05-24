<?php

namespace Wearesho\Evrotel\Yii\Console;

use yii\console;

/**
 * Class Controller
 * @package Wearesho\Evrotel\Yii\Console
 */
class Controller extends console\Controller
{
    public function actions(): array
    {
        return [
            'run' => [
                'class' => Action\CreateJobs::class,
            ],
            'check' => [
                'class' => Action\Check::class,
                'isAuto' => false,
            ],
            'check-auto' => [
                'class' => Action\Check::class,
                'isAuto' => true,
            ],
            'clean' => [
                'class' => Action\Clean::class,
            ],
            'flush' => [
                'class' => Action\Flush::class,
            ],
        ];
    }
}
