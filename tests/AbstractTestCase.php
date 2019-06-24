<?php

namespace Wearesho\Evrotel\Yii\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use Wearesho\Yii\Filesystem\Filesystem;
use yii\helpers;
use yii\phpunit;

/**
 * Class AbstractTestCase
 * @package Wearesho\Evrotel\Yii\Tests
 * @internal
 */
class AbstractTestCase extends phpunit\TestCase
{
    /** @var MockObject */
    protected $fs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fs = $this->createMock(Filesystem::class);
        $this->app->set('fs', $this->fs);
        $this->container->set(Filesystem::class, $this->fs);
    }

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
