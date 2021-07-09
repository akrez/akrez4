<?php

use yii\db\Migration;

class m210709_131918_011_create_table_product_field extends Migration
{
    public function up()
    {
        $tableName = $this->db->tablePrefix . 'product_field';
        if ($this->db->getTableSchema($tableName, true) !== null) {
            return;
        }
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%product_field}}',
            [
                'field' => $this->string(64)->notNull(),
                'value' => $this->string(64)->notNull(),
                'product_id' => $this->integer()->notNull(),
                'category_id' => $this->integer()->notNull(),
                'blog_name' => $this->string(31)->notNull(),
            ],
            $tableOptions
        );

        $this->addPrimaryKey('PRIMARYKEY', '{{%product_field}}', ['field', 'value', 'product_id']);

        $this->createIndex('product_id', '{{%product_field}}', ['product_id']);
        $this->createIndex('blog_name', '{{%product_field}}', ['blog_name']);
        $this->createIndex('category', '{{%product_field}}', ['category_id']);

        $this->addForeignKey(
            'field_string_ibfk_3',
            '{{%product_field}}',
            ['product_id'],
            '{{%product}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'product_field_ibfk_1',
            '{{%product_field}}',
            ['category_id'],
            '{{%category}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'product_field_ibfk_2',
            '{{%product_field}}',
            ['blog_name'],
            '{{%blog}}',
            ['name'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%product_field}}');
    }
}
