<?php

namespace Wearesho\Evrotel\Yii\Tests\Unit\Task;

use Carbon\Carbon;
use Horat1us\Yii\Exceptions\ModelException;
use Wearesho\Evrotel;

/**
 * Class CallTest
 * @package Wearesho\Evrotel\Yii\Tests\Unit\Task
 * @internal
 */
class CallTest extends Evrotel\Yii\Tests\AbstractTestCase
{
    protected const PHONE = '380970000000';
    protected const FILE = 'demo.wav';

    /** @var Carbon */
    protected $now;

    protected function setUp(): void
    {
        parent::setUp();
        $this->now = Carbon::now();
        Carbon::setTestNow($this->now);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Carbon::setTestNow();
    }

    /**
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     */
    public function testSchedulingTask(): void
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);

        $task = new Evrotel\Yii\Task([
            'queue_id' => 1,
            'recipient' => static::PHONE,
            'file' => static::FILE,
        ]);
        ModelException::saveOrThrow($task);

        $repeat = new Evrotel\Yii\Task\Repeat([
            'task' => $task,
            'min_duration' => 10,
            'max_count' => 2,
            'interval' => 5,
            'end_at' => Carbon::now()->addMinutes(1)->toDateTimeString(),
        ]);
        ModelException::saveOrThrow($repeat);

        $call = new Evrotel\Yii\Call([
            'from' => '1',
            'to' => static::PHONE,
            'direction' => Evrotel\Call\Direction::OUTCOME,
            'finished' => true,
            'disposition' => Evrotel\Call\Disposition::ANSWERED,
            'file' => 'demo.wav',
            'duration' => $repeat->min_duration - 1,
            'at' => Carbon::now()->toDateTimeString(),
            'is_auto' => true,
        ]);
        ModelException::saveOrThrow($call);

        $next = $task->next;
        $this->assertInstanceOf(Evrotel\Yii\Task::class, $next);
        $this->assertEquals($next->previous_id, $task->id);
        $this->assertEquals(
            $next->at,
            $this->now->copy()->addMinutes($repeat->interval)->toDateTimeString()
        );
    }

    /**
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     */
    public function testMinDurationSchedulingTask(): void
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);

        $task = new Evrotel\Yii\Task([
            'queue_id' => 1,
            'recipient' => static::PHONE,
            'file' => static::FILE,
        ]);
        ModelException::saveOrThrow($task);

        $repeat = new Evrotel\Yii\Task\Repeat([
            'task' => $task,
            'min_duration' => 10,
            'max_count' => 2,
            'interval' => 5,
            'end_at' => Carbon::now()->addMinutes(1)->toDateTimeString(),
        ]);
        ModelException::saveOrThrow($repeat);

        $call = new Evrotel\Yii\Call([
            'from' => '1',
            'to' => static::PHONE,
            'direction' => Evrotel\Call\Direction::OUTCOME,
            'finished' => true,
            'disposition' => Evrotel\Call\Disposition::ANSWERED,
            'file' => 'demo.wav',
            'duration' => $repeat->min_duration + 1,
            'at' => Carbon::now()->toDateTimeString(),
            'is_auto' => true,
        ]);
        ModelException::saveOrThrow($call);

        $this->assertNull($task->next);
    }

    /**
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     */
    public function testMissingSchedulingAfterEndAt(): void
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);

        $task = new Evrotel\Yii\Task([
            'queue_id' => 1,
            'recipient' => static::PHONE,
            'file' => static::FILE,
        ]);
        ModelException::saveOrThrow($task);

        $repeat = new Evrotel\Yii\Task\Repeat([
            'task' => $task,
            'min_duration' => 10,
            'max_count' => 2,
            'interval' => 5,
            'end_at' => Carbon::now()->subMinutes(1)->toDateTimeString(),
        ]);
        ModelException::saveOrThrow($repeat);

        $call = new Evrotel\Yii\Call([
            'from' => '1',
            'to' => static::PHONE,
            'direction' => Evrotel\Call\Direction::OUTCOME,
            'finished' => true,
            'disposition' => Evrotel\Call\Disposition::ANSWERED,
            'file' => 'demo.wav',
            'duration' => $repeat->min_duration - 1,
            'at' => Carbon::now()->toDateTimeString(),
            'is_auto' => true,
        ]);
        ModelException::saveOrThrow($call);

        $this->assertNull($task->next);
    }

    /**
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     */
    public function testMissingSchedulingAfterMaxCount(): void
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);

        $task = new Evrotel\Yii\Task([
            'queue_id' => 1,
            'recipient' => static::PHONE,
            'file' => static::FILE,
        ]);
        ModelException::saveOrThrow($task);

        $task = new Evrotel\Yii\Task([
            'queue_id' => 1,
            'recipient' => static::PHONE,
            'file' => static::FILE,
            'previous' => $task,
        ]);
        ModelException::saveOrThrow($task);

        $repeat = new Evrotel\Yii\Task\Repeat([
            'task' => $task,
            'min_duration' => 10,
            'max_count' => 2,
            'interval' => 5,
            'end_at' => Carbon::now()->addMinutes(1)->toDateTimeString(),
        ]);
        ModelException::saveOrThrow($repeat);

        $call = new Evrotel\Yii\Call([
            'from' => '1',
            'to' => static::PHONE,
            'direction' => Evrotel\Call\Direction::OUTCOME,
            'finished' => true,
            'disposition' => Evrotel\Call\Disposition::ANSWERED,
            'file' => 'demo.wav',
            'duration' => $repeat->min_duration - 1,
            'at' => Carbon::now()->toDateTimeString(),
            'is_auto' => true,
        ]);
        ModelException::saveOrThrow($call);

        $this->assertNull($task->next);
    }

    /**
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     */
    public function testMissingSchedulingWithoutRepeat(): void
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);

        $task = new Evrotel\Yii\Task([
            'queue_id' => 1,
            'recipient' => static::PHONE,
            'file' => static::FILE,
        ]);
        ModelException::saveOrThrow($task);

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

        $this->assertNull($task->next);
    }
}
