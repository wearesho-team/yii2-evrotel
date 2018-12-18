<?php

namespace Wearesho\Evrotel\Yii\Tests\Unit;

use Carbon\Carbon;
use Horat1us\Yii\Exceptions\ModelException;
use Wearesho\Evrotel;

/**
 * Class CallTest
 * @package Wearesho\Evrotel\Yii\Tests\Unit
 * @internal
 */
class CallTest extends Evrotel\Yii\Tests\AbstractTestCase
{
    protected const PHONE = '380990000000';

    /**
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     */
    public function testFoundRelatedTask(): void
    {
        $relatedTask = new Evrotel\Yii\Task([
            'recipient' => static::PHONE,
            'file' => 'demo.wav',
            'queue_id' => 1,
        ]);
        ModelException::saveOrThrow($relatedTask);

        $call = new Evrotel\Yii\Call([
            'from' => '1',
            'to' => static::PHONE,
            'direction' => Evrotel\Call\Direction::OUTCOME,
            'finished' => true,
            'disposition' => Evrotel\Call\Disposition::ANSWERED,
            'file' => 'demo.wav',
            'duration' => 10,
            'at' => Carbon::now()->toDateTimeString(),
            'is_auto' => true,
        ]);
        ModelException::saveOrThrow($call);

        $foundRelatedTask = $call->findRelatedTask();
        $this->assertInstanceOf(Evrotel\Yii\Task::class, $foundRelatedTask);
        $this->assertEquals($relatedTask->id, $foundRelatedTask->id);
    }

    /**
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     */
    public function testFoundSavedRelatedTask(): void
    {
        $relatedTask = new Evrotel\Yii\Task([
            'recipient' => static::PHONE,
            'file' => 'demo.wav',
            'queue_id' => 1,
        ]);
        ModelException::saveOrThrow($relatedTask);

        $call = new Evrotel\Yii\Call([
            'from' => '1',
            'to' => static::PHONE,
            'direction' => Evrotel\Call\Direction::OUTCOME,
            'finished' => true,
            'disposition' => Evrotel\Call\Disposition::ANSWERED,
            'file' => 'demo.wav',
            'duration' => 10,
            'at' => Carbon::now()->toDateTimeString(),
            'is_auto' => true,
        ]);
        ModelException::saveOrThrow($call);

        $foundRelatedTask = $call->findRelatedTask();
        $this->assertInstanceOf(Evrotel\Yii\Task::class, $foundRelatedTask);
        $this->assertEquals($relatedTask->id, $foundRelatedTask->id);
    }

    /**
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     */
    public function testNotFoundDueToUpdatedAt(): void
    {
        $relatedTask = new Evrotel\Yii\Task([
            'recipient' => static::PHONE,
            'file' => 'demo.wav',
            'queue_id' => 1,
        ]);
        Carbon::setTestNow(Carbon::now()->subMinutes(11));
        ModelException::saveOrThrow($relatedTask);
        Carbon::setTestNow();

        $call = new Evrotel\Yii\Call([
            'from' => '1',
            'to' => static::PHONE,
            'direction' => Evrotel\Call\Direction::OUTCOME,
            'finished' => true,
            'disposition' => Evrotel\Call\Disposition::ANSWERED,
            'file' => 'demo.wav',
            'duration' => 10,
            'at' => Carbon::now()->toDateTimeString(),
            'is_auto' => true,
        ]);
        ModelException::saveOrThrow($call);

        $foundRelatedTask = $call->findRelatedTask();
        $this->assertNull($foundRelatedTask);
    }

    /**
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     */
    public function testNotFoundDueToNullQueueId(): void
    {
        $relatedTask = new Evrotel\Yii\Task([
            'recipient' => static::PHONE,
            'file' => 'demo.wav',
        ]);
        Carbon::setTestNow(Carbon::now()->subMinutes(11));
        ModelException::saveOrThrow($relatedTask);
        Carbon::setTestNow();

        $call = new Evrotel\Yii\Call([
            'from' => '1',
            'to' => static::PHONE,
            'direction' => Evrotel\Call\Direction::OUTCOME,
            'finished' => true,
            'disposition' => Evrotel\Call\Disposition::ANSWERED,
            'file' => 'demo.wav',
            'duration' => 10,
            'at' => Carbon::now()->toDateTimeString(),
            'is_auto' => true,
        ]);
        ModelException::saveOrThrow($call);

        $foundRelatedTask = $call->findRelatedTask();
        $this->assertNull($foundRelatedTask);
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Can not find task for not-auto call
     */
    public function testTryToFindTaskForNotAutoCall(): void
    {
        $call = new Evrotel\Yii\Call([
            'is_auto' => false,
        ]);
        $call->findRelatedTask();
    }

    /**
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     */
    public function testAutoRelatingTask(): void
    {
        $task = new Evrotel\Yii\Task([
            'recipient' => static::PHONE,
            'file' => 'demo.wav',
            'queue_id' => 1,
        ]);
        ModelException::saveOrThrow($task);

        $call = new Evrotel\Yii\Call([
            'from' => '1',
            'to' => (string)static::PHONE,
            'direction' => Evrotel\Call\Direction::OUTCOME,
            'finished' => true,
            'disposition' => Evrotel\Call\Disposition::ANSWERED,
            'file' => 'demo.wav',
            'duration' => 10,
            'at' => Carbon::now()->toDateTimeString(),
            'is_auto' => true,
        ]);
        ModelException::saveOrThrow($call);

        $this->assertInstanceOf(Evrotel\Yii\Call::class, $task->call);
        $this->assertEquals($call->id, $task->call->id);

        $this->assertInstanceOf(Evrotel\Yii\Task::class, $call->task);
        $this->assertEquals($task->id, $call->task->id);
    }
}
