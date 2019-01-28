<?php

declare(strict_types=1);

namespace Wearesho\Evrotel\Yii\Tests\Unit\Console\Action;

use Horat1us\Yii\Exceptions\ModelException;
use Horat1us\Yii\Interfaces\ModelExceptionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use yii\console;
use yii\helpers;
use Wearesho\Evrotel;

/**
 * Class CleanTest
 * @package Wearesho\Evrotel\Yii\Tests\Unit\Console\Action
 * @internals
 */
class CleanTest extends Evrotel\Yii\Tests\AbstractTestCase
{
    /** @var Evrotel\Yii\Console\Action\Clean */
    protected $action;

    /** @var MockObject */
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = $this->createMock(console\Controller::class);
        $this->action = new Evrotel\Yii\Console\Action\Clean('clean', $this->controller);
    }

    public function testMovingWaitingToClosedTasks(): void
    {
        $task = new Evrotel\Yii\Task([
            'recipient' => '380000000000',
            'file' => 'test.wav',
            'status' => Evrotel\Yii\Task::STATUS_WAITING
        ]);
        $task->save();
        $this->controller->expects($this->once())->method('stdout')->with(
            "Removed 1 tasks" . PHP_EOL,
            helpers\Console::FG_GREEN
        );
        $this->action->run();
        $task->refresh();
        $this->assertEquals(
            Evrotel\Yii\Task::STATUS_CLOSED,
            $task->status
        );
    }

    public function testNotMovingAnotherTasks(): void
    {
        $statuses = [
            Evrotel\Yii\Task::STATUS_PROCESS,
            Evrotel\Yii\Task::STATUS_ERROR
        ];
        /** @var Evrotel\Yii\Task[] $tasks */
        $tasks = [];
        foreach ($statuses as $status) {
            $tasks[] = $task = new Evrotel\Yii\Task([
                'recipient' => '380000000000',
                'file' => 'test.wav',
                'status' => $status
            ]);
            $task->save();
        }
        $this->controller->expects($this->once())->method('stdout')->with(
            "Removed 0 tasks" . PHP_EOL,
            helpers\Console::FG_GREEN
        );
        $this->action->run();

        foreach ($tasks as $task) {
            $task->refresh();
            $this->assertNotEquals(Evrotel\Yii\Task::STATUS_CLOSED, $task->status);
        }
    }
}
