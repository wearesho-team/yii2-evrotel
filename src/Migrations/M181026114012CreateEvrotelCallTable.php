<?php

namespace Wearesho\Evrotel\Yii\Migrations;

use yii\db\Migration;
use Wearesho\Evrotel;

/**
 * Class M181026114012CreateEvrotelCallTable
 */
class M181026114012CreateEvrotelCallTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->execute("CREATE TYPE evrotel_direction AS ENUM('i', 'o');");

        $disposition = [
            Evrotel\Call\Disposition::NO_ANSWER,
            Evrotel\Call\Disposition::FAILED,
            Evrotel\Call\Disposition::CONGESTION,
            Evrotel\Call\Disposition::BUSY,
            Evrotel\Call\Disposition::ANSWERED,
        ];
        $enum = implode(',', array_map(function (string $element): string {
            return "'$element'";
        }, $disposition));

        $this->execute("CREATE TYPE evrotel_disposition AS ENUM({$enum})");

        $this->createTable('evrotel_call', [
            'id' => $this->primaryKey(),
            'from' => $this->string()->notNull(),
            'to' => $this->string()->notNull(),
            'direction' => 'evrotel_direction NOT NULL',
            'finished' => $this->boolean()->notNull()->defaultValue(false),
            'disposition' => 'evrotel_disposition NOT NULL',
            'file' => $this->text()->null(),
            'duration' => $this->integer()->unsigned()->null(),
            'at' => $this->timestamp()->notNull(),
            'created_at' => $this->timestamp()->notNull(),
            'updated_at' => $this->timestamp()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('evrotel_call');

        $this->execute('DROP TYPE evrotel_disposition');
        $this->execute('DROP TYPE evrotel_direction');
    }
}
