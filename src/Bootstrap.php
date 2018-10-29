<?php

namespace Wearesho\Evrotel\Yii;

use Wearesho\Evrotel;
use yii\base;
use yii\console;
use yii\web;
use yii\di;

/**
 * Class Bootstrap
 * @package Wearesho\Evrotel\Yii
 */
class Bootstrap extends base\BaseObject implements base\BootstrapInterface
{
    /**
     * @var array|string|Evrotel\Yii\Web\Bootstrap Need to configure web\Application with controller
     * Set to null to skip configuring web\Application
     */
    public $web = [
        'class' => Evrotel\Yii\Web\Bootstrap::class,
    ];

    /**
     * @see Evrotel\Yii\Console\Controller
     * @var array|string|Evrotel\Yii\Console\Bootstrap Need to configure console\Application with controller
     * Set to null to skip configuring console\Application
     */
    public $console = [
        'class' => Evrotel\Yii\Console\Bootstrap::class,
    ];

    /**
     * Default config, will be used in container interface definition
     * @see Evrotel\Yii\ConfigInterface
     * @var array|string|ConfigInterface
     */
    public $config = [
        'class' => Evrotel\Yii\EnvironmentConfig::class,
    ];

    /**
     * @inheritdoc
     * @throws base\InvalidConfigException
     */
    public function bootstrap($app): void
    {
        if (!is_null($this->web) && $app instanceof web\Application) {
            /** @var Evrotel\Yii\Web\Bootstrap $bootstrap */
            $bootstrap = di\Instance::ensure($this->web, Evrotel\Yii\Web\Bootstrap::class);
            $bootstrap->bootstrap($app);
        }

        if (!is_null($this->console) && $app instanceof console\Application) {
            /** @var Evrotel\Yii\Console\Bootstrap $bootstrap */
            $bootstrap = di\Instance::ensure($this->console, Evrotel\Yii\Console\Bootstrap::class);
            $bootstrap->bootstrap($app);
        }

        $this->configure(\Yii::$container);
    }

    public function configure(di\Container $container): void
    {
        $container->set(ConfigInterface::class, $this->config);
    }
}
