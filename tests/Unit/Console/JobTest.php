<?php

namespace Wearesho\Evrotel\Yii\Tests\Unit\Console;

use GuzzleHttp;
use yii\queue;
use Wearesho\Evrotel;

/**
 * Class JobTest
 * @package Wearesho\Evrotel\Yii\Tests\Unit\Console
 * @internal
 */
class JobTest extends Evrotel\Yii\Tests\AbstractTestCase
{
    protected const PHONE = '380970000000';
    protected const FILE = 'demo.wav';

    /** @var Evrotel\AutoDial\MediaRepository */
    protected $repository;

    /** @var queue\Queue */
    protected $queue;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new class extends Evrotel\AutoDial\MediaRepository
        {
            /** @var string[] */
            public $pushed = [];

            /** @noinspection PhpMissingParentConstructorInspection */
            public function __construct()
            {
            }

            public function push(string $link): string
            {
                $this->pushed[] = $link;
                return $link;
            }
        };
        $this->queue = new class extends queue\sync\Queue
        {
            /** @var queue\JobInterface[] */
            public $pushed = [];

            public function push($job)
            {
                $this->pushed[] = $job;
                return count($this->pushed) - 1;
            }
        };
    }

    public function testPushingMediaAndJob(): void
    {
        $request = new Evrotel\AutoDial\Request(
            static::PHONE,
            static::FILE
        );

        $job = new Evrotel\Yii\Console\Job([
            'repository' => $this->repository,
            'request' => $request,
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $job->execute($this->queue);

        $this->assertCount(1, $this->repository->pushed);
        $this->assertEquals($request->getFileName(), $this->repository->pushed[0]);

        $this->assertCount(1, $this->queue->pushed);
        $this->assertEquals(
            new Evrotel\Yii\Console\DialJob(['request' => $request]),
            $this->queue->pushed[0]
        );

        $this->assertTrue(
            \Yii::$app->cache->exists([
                'type' => Evrotel\Yii\Console\Job::class,
                'fileName' => $request->getFileName()
            ])
        );
    }

    public function testPushingOnlyDialJobIfCacheExists(): void
    {
        $request = new Evrotel\AutoDial\Request(
            static::PHONE,
            static::FILE
        );

        $job = new Evrotel\Yii\Console\Job([
            'repository' => $this->repository,
            'request' => $request,
        ]);

        \Yii::$app->cache->set([
            'type' => Evrotel\Yii\Console\Job::class,
            'fileName' => $request->getFileName()
        ], true);

        /** @noinspection PhpUnhandledExceptionInspection */
        $job->execute($this->queue);

        $this->assertCount(0, $this->repository->pushed);

        $this->assertCount(1, $this->queue->pushed);
        $this->assertEquals(
            new Evrotel\Yii\Console\DialJob(['request' => $request]),
            $this->queue->pushed[0]
        );
    }
}
