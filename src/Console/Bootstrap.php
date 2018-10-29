<?php

namespace Wearesho\Evrotel\Yii\Console;

use Horat1us\Yii\Traits\BootstrapMigrations;
use yii\base;
use yii\console;

/**
 * Class Bootstrap
 * @package Wearesho\Evrotel\Yii\Console
 */
class Bootstrap implements base\BootstrapInterface
{
    use BootstrapMigrations;

    /** @var bool Need to configure migrations controller */
    public $migrations = true;

    /** @var bool Need to configure queue controler */
    public $queue = true;

    /**
     * @inheritdoc
     * @throws base\InvalidConfigException
     */
    public function bootstrap($app): void
    {
        if (!$app instanceof console\Application) {
            throw new base\InvalidConfigException(console\Application::class . " can by only configured");
        }

        if ($this->migrations) {
            $this->appendMigrations($app, 'Wearesho\\Evrotel\\Yii\\Migrations');
        }

        if ($this->queue) {
            $app->controllerMap['evrotel'] = [
                'class' => Controller::class,
            ];
        }
    }
}
