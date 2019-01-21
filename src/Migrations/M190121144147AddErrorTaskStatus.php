<?php

namespace Wearesho\Evrotel\Yii\Migrations;

use yii\db\Migration;

/**
 * Class M190121144147AddErrorTaskStatus
 */
class M190121144147AddErrorTaskStatus extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(): void
    {
        if ($this->db->driverName !== 'pgsql') {
            // enum only supported for pgsql
            return;
        }

        $this->execute(/** @lang PostgreSQL */
            "ALTER TYPE evrotel_task_status ADD VALUE 'error';"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        if ($this->db->driverName !== 'pgsql') {
            // enum only supported for pgsql
            return;
        }

        $this->execute(/** @lang PostgreSQL */
            "ALTER TYPE evrotel_task_status RENAME TO evrotel_task_status_old"
        );
        $this->execute(/** @lang PostgreSQL */
            "CREATE TYPE evrotel_task_status AS ENUM('waiting','process','closed');"
        );
        $this->execute("ALTER TABLE evrotel_task ALTER COLUMN status type varchar(7);");
        $this->execute("ALTER TABLE evrotel_task ALTER COLUMN status SET DEFAULT 'waiting' :: evrotel_task_status;");
        $this->execute(/** @lang PostgreSQL */
            <<<QUERY
ALTER TABLE evrotel_task ALTER COLUMN status TYPE evrotel_task_status  USING status::varchar::evrotel_task_status
QUERY
        );
        $this->execute(/** @lang PostgreSQL */
            "DROP TYPE evrotel_task_status_old"
        );
    }
}
