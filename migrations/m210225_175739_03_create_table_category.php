<?php

use yii\db\Migration;

class m210225_175739_03_create_table_category extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%category}}',
            [
                'id' => $this->primaryKey(),
                'updated_at' => $this->integer(),
                'title' => $this->string(64)->notNull(),
                'params' => $this->text(),
                'user_name' => $this->string(60),
                'status' => $this->tinyInteger(4)->notNull(),
            ],
            $tableOptions
        );

        $this->createIndex('user_name', '{{%category}}', ['user_name']);
        $this->createIndex('title', '{{%category}}', ['title', 'user_name'], true);

        $this->addForeignKey(
            'category_ibfk_1',
            '{{%category}}',
            ['user_name'],
            '{{%user}}',
            ['name'],
            'SET NULL',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%category}}');
    }
}
