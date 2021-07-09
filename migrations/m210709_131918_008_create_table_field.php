<?php

use yii\db\Migration;

class m210709_131918_008_create_table_field extends Migration
{
    public function up()
    {
        $tableName = $this->db->tablePrefix . 'field';
        if ($this->db->getTableSchema($tableName, true) !== null) {
            return;
        }
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
                'blog_name' => $this->string(31),
                'unit' => $this->string(64),
            ],
            $tableOptions
        );

        $this->createIndex('title', '{{%field}}', ['title', 'category_id', 'blog_name'], true);
        $this->createIndex('blog_name', '{{%field}}', ['blog_name']);
        $this->createIndex('category_id', '{{%field}}', ['category_id']);

        $this->addForeignKey(
            'field_ibfk_1',
            '{{%field}}',
            ['blog_name'],
            '{{%blog}}',
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
