<?php

use yii\db\Migration;

class m210709_131917_006_create_table_color extends Migration
{
    public function up()
    {
        $tableName = $this->db->tablePrefix . 'color';
        if ($this->db->getTableSchema($tableName, true) !== null) {
            return;
        }
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%color}}',
            [
                'id' => $this->primaryKey(),
                'title' => $this->string(31)->notNull(),
                'code' => $this->string(31)->notNull(),
                'blog_name' => $this->string(60),
            ],
            $tableOptions
        );

        $this->createIndex('blog_name', '{{%color}}', ['blog_name']);

        $this->addForeignKey(
            'color_ibfk_1',
            '{{%color}}',
            ['blog_name'],
            '{{%blog}}',
            ['name'],
            'SET NULL',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%color}}');
    }
}
