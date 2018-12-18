<?php

namespace Wearesho\Evrotel\Yii\Migrations;

use yii\db\Migration;

/**
 * Class M181218124247AddIsAutoToEvrotelCallTable
 */
class M181218124247AddIsAutoToEvrotelCallTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->addColumn(
            'evrotel_call',
            'is_auto',
            $this->boolean()->notNull()->defaultValue(false)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropColumn('evrotel_call', 'is_auto');
    }
}
