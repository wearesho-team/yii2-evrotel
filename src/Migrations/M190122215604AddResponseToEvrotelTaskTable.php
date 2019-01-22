<?php

namespace Wearesho\Evrotel\Yii\Migrations;

use yii\db\Migration;

/**
 * Class M190122215604AddResponseToEvrotelTaskTable
 */
class M190122215604AddResponseToEvrotelTaskTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->addColumn(
            'evrotel_task',
            'response',
            $this->text()->null()->comment('Response from autodial server')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropColumn(
            'evrotel_task',
            'response'
        );
    }
}
