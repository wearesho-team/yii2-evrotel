<?php

namespace Wearesho\Evrotel\Yii\Migrations;

use yii\db\Migration;

/**
 * Class M181218113236CreateEvrotelTaskRepeatTable
 */
class M181218113236CreateEvrotelTaskRepeatTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable(
            'evrotel_task_repeat',
            [
                'evrotel_task_id' => $this->integer()->notNull(),
                'min_duration' => $this->integer()->unsigned()->notNull(),
                'max_count' => $this->integer()->unsigned()->notNull(),
                'interval' => $this->integer()->unsigned()->notNull(),
                'end_at' => $this->timestamp()->notNull(),
            ]
        );

        $this->addForeignKey(
            'fk_evrotel_task_repeat_task',
            'evrotel_task_repeat',
            'evrotel_task_id',
            'evrotel_task',
            'id',
            'cascade',
            'cascade'
        );

        $this->addPrimaryKey(
            'pk_evrotel_task_repeat',
            'evrotel_task_repeat',
            'evrotel_task_id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('evrotel_task_repeat');
    }
}
