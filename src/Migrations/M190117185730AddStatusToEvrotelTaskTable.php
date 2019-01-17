<?php

namespace Wearesho\Evrotel\Yii\Migrations;

use yii\db\Migration;

/**
 * Class M190117185730AddStatusToEvrotelTaskTable
 */
class M190117185730AddStatusToEvrotelTaskTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        if ($this->db->driverName === 'pgsql') {
            $this->execute(/** @lang PostgreSQL */
                "CREATE TYPE evrotel_task_status AS ENUM('waiting','process','closed');"
            );
            $enum = 'evrotel_task_status NOT NULL DEFAULT \'waiting\' :: evrotel_task_status';
        } else {
            $enum = $this->string(7)->defaultValue('waiting')->notNull();
        }

        $this->addColumn(
            'evrotel_task',
            'status',
            $enum
        );

        $this->update('evrotel_task', ['status' => 'closed'], ['is not', 'queue_id', null]);

        $this->createIndex(
            'i_evrotel_task_status',
            'evrotel_task',
            'status'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropColumn('evrotel_task', 'status');
        if ($this->db->driverName === 'pgsql') {
            $this->execute(/** @lang PostgreSQL */
                'DROP TYPE evrotel_task_status;'
            );
        }
    }
}
