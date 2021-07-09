<?php

use yii\db\Migration;

class m210709_131918_010_create_table_product extends Migration
{
    public function up()
    {
        $tableName = $this->db->tablePrefix . 'product';
        if ($this->db->getTableSchema($tableName, true) !== null) {
            return;
        }
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%product}}',
            [
                'id' => $this->primaryKey(),
                'updated_at' => $this->integer(),
                'created_at' => $this->integer(),
                'status' => $this->tinyInteger(4)->notNull(),
                'title' => $this->string(64)->notNull(),
                'code' => $this->string(31),
                'price_min' => $this->double(),
                'price_max' => $this->double(),
                'des' => $this->string(160),
                'view' => $this->integer()->defaultValue('0'),
                'params' => $this->text(),
                'image' => $this->string(16),
                'category_id' => $this->integer(),
                'blog_name' => $this->string(31),
            ],
            $tableOptions
        );

        $this->createIndex('blog_name', '{{%product}}', ['blog_name']);
        $this->createIndex('category_id', '{{%product}}', ['category_id']);
        $this->createIndex('image', '{{%product}}', ['image']);

        $this->addForeignKey(
            'product_ibfk_1',
            '{{%product}}',
            ['category_id'],
            '{{%category}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'product_ibfk_2',
            '{{%product}}',
            ['blog_name'],
            '{{%blog}}',
            ['name'],
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'product_ibfk_3',
            '{{%product}}',
            ['image'],
            '{{%gallery}}',
            ['name'],
            'SET NULL',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%product}}');
    }
}
