<?php

namespace Wearesho\Evrotel\Yii\Tests\Unit\Console\Action;

use Horat1us\Yii\Exceptions\ModelException;
use Wearesho\Evrotel;
use Wearesho\Yii\Filesystem\Filesystem;
use yii\base;
use yii\queue;

/**
 * Class CreateJobsTest
 * @package Wearesho\Evrotel\Yii\Tests\Unit\Console\Action
 */
class CreateJobsTest extends Evrotel\Yii\Tests\AbstractTestCase
{
    public function testNotCreatingTasksIfNoChannelsAvailable(): void
    {
        $config = new Evrotel\Yii\Config([
            'channels' => 2,
        ]);

        // task to block channel
        $task = new Evrotel\Yii\Task([
            'recipient' => '380000000000',
            'file' => 'test.wav',
            'status' => Evrotel\Yii\Task::STATUS_PROCESS,
        ]);
        ModelException::saveOrThrow($task);

        // task to block channel
        $task = new Evrotel\Yii\Task([
            'recipient' => '380000000000',
            'file' => 'test.wav',
            'status' => Evrotel\Yii\Task::STATUS_PROCESS,
        ]);
        ModelException::saveOrThrow($task);

        // task to be pushed queue (if channels would be available)
        $task = new Evrotel\Yii\Task([
            'recipient' => '380000000000',
            'file' => 'test.wav',
            'status' => Evrotel\Yii\Task::STATUS_WAITING,
        ]);
        ModelException::saveOrThrow($task);

        $controller = new base\Controller('evrotel', new base\Module('app'));
        $queue = $this->createMock(queue\sync\Queue::class);
        $queue->expects($this->never())->method($this->anything());

        $action = new Evrotel\Yii\Console\Action\CreateJobs('create', $controller, [
            'config' => $config,
            'queue' => $queue,
            'fs' => $this->createMock(Filesystem::class),
        ]);
        $action->run();
    }

    public function testCreatingTasksIfAvailable(): void
    {
        $config = new Evrotel\Yii\Config([
            'channels' => 1,
        ]);

        // task to be pushed queue
        $task = new Evrotel\Yii\Task([
            'recipient' => '380000000000',
            'file' => 'test.wav',
            'status' => Evrotel\Yii\Task::STATUS_WAITING,
        ]);
        ModelException::saveOrThrow($task);

        $controller = new base\Controller('evrotel', new base\Module('app'));
        $queue = $this->createMock(queue\sync\Queue::class);
        $queue->expects($this->exactly(1))->method('push')->with(
            new Evrotel\Yii\Console\Job([
                'request' => new Evrotel\AutoDial\Request($task->recipient, $task->file),
            ])
        )->willReturn($queueJobId = 1);

        $fs = $this->createMock(Filesystem::class);
        $fs->method('getUrl')->willReturn('test.wav');

        $action = new Evrotel\Yii\Console\Action\CreateJobs('create', $controller, [
            'config' => $config,
            'queue' => $queue,
            'fs' => $fs,
        ]);
        $action->run();

        $task->refresh();
        $this->assertEquals($queueJobId, $task->queue_id);
        $this->assertEquals(
            Evrotel\Yii\Task::STATUS_PROCESS,
            $task->status
        );
    }
}
