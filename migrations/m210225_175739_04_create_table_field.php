<?php

use yii\db\Migration;

class m210225_175739_04_create_table_field extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%field}}',
            [
                'id' => $this->primaryKey(),
                'title' => $this->string(64)->notNull(),
                'seq' => $this->integer(),
                'in_summary' => $this->boolean()->defaultValue('1'),
                'params' => $this->text(),
                'category_id' => $this->integer(),
                'user_name' => $this->string(31),
                'unit' => $this->string(64),
            ],
            $tableOptions
        );

        $this->createIndex('user_name', '{{%field}}', ['user_name']);
        $this->createIndex('title', '{{%field}}', ['title', 'category_id', 'user_name'], true);
        $this->createIndex('category_id', '{{%field}}', ['category_id']);

        $this->addForeignKey(
            'field_ibfk_1',
            '{{%field}}',
            ['user_name'],
            '{{%user}}',
            ['name'],
            'SET NULL',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%field}}');
    }
}
