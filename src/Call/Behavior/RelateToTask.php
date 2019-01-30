<?php

namespace Wearesho\Evrotel\Yii\Call\Behavior;

use yii\db;
use yii\base;
use Horat1us\Yii\Exceptions\ModelException;
use Wearesho\Evrotel;

/**
 * Class RelateToTask
 * @package Wearesho\Evrotel\Yii\Call\Behavior
 */
class RelateToTask extends Evrotel\Yii\Call\Behavior
{
    public function events(): array
    {
        return [
            db\ActiveRecord::EVENT_AFTER_INSERT => 'relate',
            db\ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
        ];
    }

    /**
     * @param db\AfterSaveEvent $event
     * @throws base\InvalidConfigException
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     */
    public function relate(db\AfterSaveEvent $event): void
    {
        $call = $this->extractCall($event);
        if (!$call->is_auto) {
            \Yii::debug("Skip finding related task for not-auto call {$call->id}", static::class);
            return;
        }

        $task = $call->findRelatedTask();
        if (!$task instanceof Evrotel\Yii\Task) {
            \Yii::warning("Can not find related task for call {$call->id}", static::class);
            return;
        }

        $relation = new Evrotel\Yii\Task\Call([
            'call' => $call,
            'task' => $task,
        ]);
        ModelException::saveOrThrow($relation);

        $task->populateRelation('call', $call);
        $call->populateRelation('task', $task);
    }

    /**
     * @param db\AfterSaveEvent $event
     * @throws \Horat1us\Yii\Interfaces\ModelExceptionInterface
     * @throws base\InvalidConfigException
     */
    public function afterUpdate(db\AfterSaveEvent $event): void
    {
        $call = $this->extractCall($event);
        $isAutoChanged =
            (isset($event->changedAttributes['is_auto']) || \array_key_exists('is_auto', $event->changedAttributes))
            && !$event->changedAttributes['is_auto']
            && $call->is_auto;

        if (!$isAutoChanged) {
            return;
        }

        $this->relate($event);
    }
}
