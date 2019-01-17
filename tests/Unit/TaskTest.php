<?php

namespace Wearesho\Evrotel\Yii\Tests\Unit;

use Horat1us\Yii\Exceptions\ModelException;
use Wearesho\Evrotel;
use yii\base\ModelEvent;

/**
 * Class TaskTest
 * @package Wearesho\Evrotel\Yii\Tests\Unit
 * @internal
 */
class TaskTest extends Evrotel\Yii\Tests\AbstractTestCase
{
    /**
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     */
    public function testNumberForFirstTask(): void
    {
        $task = new Evrotel\Yii\Task([
            'recipient' => '380970000000',
            'file' => 'demo.wav',
        ]);
        ModelException::saveOrThrow($task);
        $this->assertEquals(1, $task->number);
    }

    /**
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     */
    public function testNumberForThirdTask(): void
    {
        $first = new Evrotel\Yii\Task([
            'recipient' => '380970000000',
            'file' => 'demo.wav',
        ]);
        ModelException::saveOrThrow($first);

        $second = new Evrotel\Yii\Task([
            'recipient' => '380970000000',
            'file' => 'demo.wav',
            'previous' => $first,
        ]);
        ModelException::saveOrThrow($second);
        $this->assertEquals(2, $second->number);

        $third = new Evrotel\Yii\Task([
            'recipient' => '380970000000',
            'file' => 'demo.wav',
            'previous' => $second,
        ]);
        ModelException::saveOrThrow($third);
        $this->assertEquals(3, $third->number);
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Number can be counted only for saved records
     */
    public function testNumberForNotSavedTask(): void
    {
        $task = new Evrotel\Yii\Task;
        $task->number;
    }

    public function testChangingStatusAfterPushingToQueue(): void
    {
        $task = new Evrotel\Yii\Task([
            'recipient' => '380970000000',
            'file' => 'demo.wav',
        ]);
        ModelException::saveOrThrow($task);

        $this->assertEquals(Evrotel\Yii\Task::STATUS_WAITING, $task->status);

        $task->queue_id = 1;
        ModelException::saveOrThrow($task);

        $this->assertEquals(Evrotel\Yii\Task::STATUS_PROCESS, $task->status);

        $task->status = Evrotel\Yii\Task::STATUS_CLOSED;
        $task->queue_id = 2;
        ModelException::saveOrThrow($task);

        $this->assertEquals(Evrotel\Yii\Task::STATUS_CLOSED, $task->status);
    }
}
