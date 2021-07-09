<?php

use yii\db\Migration;

class m210709_131918_009_create_table_page extends Migration
{
    public function up()
    {
        $tableName = $this->db->tablePrefix . 'page';
        if ($this->db->getTableSchema($tableName, true) !== null) {
            return;
        }
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%page}}',
            [
                'id' => $this->primaryKey(),
                'updated_at' => $this->integer(),
                'created_at' => $this->integer(),
                'status' => $this->tinyInteger(4)->notNull(),
                'body' => $this->text(),
                'entity' => $this->string(15)->notNull(),
                'page_type' => $this->string(31)->notNull(),
                'entity_id' => $this->string(15)->notNull(),
                'blog_name' => $this->string(31),
            ],
            $tableOptions
        );

        $this->createIndex('blog_name', '{{%page}}', ['blog_name']);

        $this->addForeignKey(
            'page_ibfk_2',
            '{{%page}}',
            ['blog_name'],
            '{{%blog}}',
            ['name'],
            'SET NULL',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%page}}');
    }
}
