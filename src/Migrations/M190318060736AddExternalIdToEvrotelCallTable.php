<?php

namespace Wearesho\Evrotel\Yii\Migrations;

use yii\db\Migration;

/**
 * Class M190318060736AddExternalIdToEvrotelCallTable
 */
class M190318060736AddExternalIdToEvrotelCallTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->addColumn(
            'evrotel_call',
            'external_id',
            $this->integer()->null()->unsigned()
        );

        $this->createIndex(
            'unique_evrotel_call_external_id',
            'evrotel_call',
            'external_id',
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropColumn('evrotel_call', 'external_id');
    }
}
