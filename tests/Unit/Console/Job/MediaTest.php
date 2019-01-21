<?php

namespace Wearesho\Evrotel\Yii\Tests\Unit\Console\Job;

use GuzzleHttp;
use Horat1us\Yii\Exceptions\ModelException;
use yii\queue;
use yii\caching;
use Wearesho\Evrotel;

/**
 * Class JobTest
 * @package Wearesho\Evrotel\Yii\Tests\Unit\Console
 * @internal
 */
class MediaTest extends Evrotel\Yii\Tests\AbstractTestCase
{
    protected const PHONE = '380970000000';
    protected const FILE = 'demo.wav';

    public function testPushingMediaAndJob(): void
    {
        $task = $this->getTask();

        $url = 'https://foo.bar/' . static::FILE;

        $this->fs->expects($this->exactly(1))
            ->method('getUrl')
            ->with(static::FILE)
            ->willReturn($url);

        $repository = $this->createMock(Evrotel\AutoDial\MediaRepository::class);
        $repository
            ->expects($this->exactly(1))
            ->method('push')
            ->with($url)
            ->willReturn(static::FILE);

        $queue = $this->createMock(queue\sync\Queue::class);
        $queue
            ->expects($this->exactly(1))
            ->method('push')
            ->with(new Evrotel\Yii\Console\Job\Dial([
                'taskId' => $task->id,
            ]))
            ->willReturn(1);

        $cache = $this->createMock(caching\ArrayCache::class);
        $cache
            ->expects($this->exactly(1))
            ->method('set')
            ->with([
                'type' => Evrotel\Yii\Console\Job\Media::class,
                'fileName' => $url,
            ], true, 43200, null);
        $cache
            ->expects($this->exactly(1))
            ->method('exists')
            ->with([
                'type' => Evrotel\Yii\Console\Job\Media::class,
                'fileName' => $url,
            ])
            ->willReturn(false);

        $job = new Evrotel\Yii\Console\Job\Media([
            'cache' => $cache,
            'repository' => $repository,
            'taskId' => $task->id,
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $job->execute($queue);
    }

    public function testPushingOnlyDialJobIfCacheExists(): void
    {
        $task = $this->getTask();

        $this->fs->expects($this->exactly(1))
            ->method('getUrl')
            ->with(static::FILE)
            ->willReturn(static::FILE);

        $repository = $this->createMock(Evrotel\AutoDial\MediaRepository::class);
        $repository
            ->expects($this->never())
            ->method('push');

        $cache = $this->createMock(caching\ArrayCache::class);
        $cache
            ->expects($this->exactly(1))
            ->method('exists')
            ->with([
                'type' => Evrotel\Yii\Console\Job\Media::class,
                'fileName' => static::FILE,
            ])
            ->willReturn(true);

        $queue = $this->createMock(queue\sync\Queue::class);
        $queue
            ->expects($this->exactly(1))
            ->method('push')
            ->with(new Evrotel\Yii\Console\Job\Dial([
                'taskId' => $task->id,
            ]))
            ->willReturn(1);

        $job = new Evrotel\Yii\Console\Job\Media([
            'repository' => $repository,
            'cache' => $cache,
            'taskId' => $task->id,
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $job->execute($queue);
    }

    protected function getTask(): Evrotel\Yii\Task
    {
        $task = new Evrotel\Yii\Task([
            'recipient' => static::PHONE,
            'file' => static::FILE,
        ]);
        ModelException::saveOrThrow($task);
        return $task;
    }
}
