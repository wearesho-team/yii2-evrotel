<?php

namespace Wearesho\Evrotel\Yii\Web;

use yii\base;
use yii\web;

/**
 * Class Bootstrap
 * @package Wearesho\Evrotel\Yii\Web
 */
class Bootstrap implements base\BootstrapInterface
{

    /**
     * @inheritdoc
     * @throws base\InvalidConfigException
     */
    public function bootstrap($app): void
    {
        if (!$app instanceof web\Application) {
            throw new base\InvalidConfigException(web\Application::class . " only can be confirgured");
        }

        $app->controllerMap['evrotel'] = [
            'class' => Controller::class,
        ];
    }
}
