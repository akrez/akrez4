<?php

use yii\db\Migration;

class m210709_131917_005_create_table_category extends Migration
{
    public function up()
    {
        $tableName = $this->db->tablePrefix . 'category';
        if ($this->db->getTableSchema($tableName, true) !== null) {
            return;
        }
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
                'blog_name' => $this->string(60),
                'status' => $this->tinyInteger(4)->notNull(),
            ],
            $tableOptions
        );

        $this->createIndex('blog_name', '{{%category}}', ['blog_name']);
        $this->createIndex('title', '{{%category}}', ['title', 'blog_name'], true);

        $this->addForeignKey(
            'category_ibfk_1',
            '{{%category}}',
            ['blog_name'],
            '{{%blog}}',
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
