<?php

namespace Wearesho\Evrotel\Yii\Migrations;

use yii\db\Migration;

/**
 * Class M181026114037CreateEvrotelTaskTable
 */
class M181026114037CreateEvrotelTaskTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('evrotel_task', [
            'id' => $this->primaryKey(),
            'queue_id' => $this->integer()->unsigned()->null(),
            'recipient' => $this->string(12)->notNull(),
            'file' => $this->text()->notNull(),
            'created_at' => $this->timestamp()->notNull(),
            'updated_at' => $this->timestamp()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('evrotel_task');
    }
}
