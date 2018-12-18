<?php

namespace Wearesho\Evrotel\Yii\Migrations;

use yii\db\Migration;

/**
 * Class M181218113001CreateEvrotelTaskCallTable
 */
class M181218113001CreateEvrotelTaskCallTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable(
            'evrotel_task_call',
            [
                'evrotel_task_id' => $this->integer()->notNull(),
                'evrotel_call_id' => $this->integer()->notNull(),
            ]
        );

        $this->addForeignKey(
            'fk_evrotel_task_call_task',
            'evrotel_task_call',
            'evrotel_task_id',
            'evrotel_task',
            'id',
            'cascade',
            'cascade'
        );

        $this->addForeignKey(
            'fk_evrotel_task_call_call',
            'evrotel_task_call',
            'evrotel_call_id',
            'evrotel_call',
            'id',
            'cascade',
            'cascade'
        );

        $this->addPrimaryKey(
            'pk_evrotel_task_call',
            'evrotel_task_call',
            ['evrotel_task_id', 'evrotel_call_id',]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('evrotel_task_call');
    }
}
