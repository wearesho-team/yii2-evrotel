<?php

namespace Wearesho\Evrotel\Yii\Migrations;

use yii\db\Migration;

/**
 * Class M181218130527AddAtToEvrotelTaskTable
 */
class M181218130527AddAtToEvrotelTaskTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->addColumn(
            'evrotel_task',
            'at',
            $this->timestamp()->null()->comment('Queue Job will not be created before this timestamp')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropColumn('evrotel_task', 'at');
    }
}
