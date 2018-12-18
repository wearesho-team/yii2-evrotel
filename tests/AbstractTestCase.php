<?php

namespace Wearesho\Evrotel\Yii\Tests;

use yii\helpers;
use yii\phpunit;

/**
 * Class AbstractTestCase
 * @package Wearesho\Evrotel\Yii\Tests
 * @internal
 */
class AbstractTestCase extends phpunit\TestCase
{
    public function globalFixtures(): array
    {
        $fixtures = [
            [
                'class' => phpunit\MigrateFixture::class,
                'migrationNamespaces' => [
                    'Wearesho\\Evrotel\\Yii\\Migrations',
                ],
            ]
        ];
        return helpers\ArrayHelper::merge(parent::globalFixtures(), $fixtures);
    }
}
