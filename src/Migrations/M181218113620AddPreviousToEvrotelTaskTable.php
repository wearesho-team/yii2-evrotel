<?php

namespace Wearesho\Evrotel\Yii\Migrations;

use yii\db\Migration;

/**
 * Class M181218113620AddPreviousToEvrotelTaskTable
 */
class M181218113620AddPreviousToEvrotelTaskTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->addColumn(
            'evrotel_task',
            'previous_id',
            $this->integer()->null()->comment('Previous Repeat Task')
        );

        $this->addForeignKey(
            'fk_evrotel_task_previous',
            'evrotel_task',
            'previous_id',
            'evrotel_task',
            'id',
            'set null',
            'cascade'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropColumn('evrotel_task', 'previous_id');
    }
}
