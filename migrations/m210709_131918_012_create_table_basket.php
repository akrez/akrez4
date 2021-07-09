<?php

use yii\db\Migration;

class m210709_131918_012_create_table_basket extends Migration
{
    public function up()
    {
        $tableName = $this->db->tablePrefix . 'basket';
        if ($this->db->getTableSchema($tableName, true) !== null) {
            return;
        }
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%basket}}',
            [
                'id' => $this->primaryKey(),
                'updated_at' => $this->integer(),
                'status' => $this->string(12)->notNull(),
                'price' => $this->double()->notNull(),
                'cnt' => $this->integer()->notNull(),
                'product_id' => $this->integer()->notNull(),
                'package_id' => $this->integer(),
                'customer_id' => $this->integer()->notNull(),
                'blog_name' => $this->string(31),
            ],
            $tableOptions
        );

        $this->createIndex('package_id', '{{%basket}}', ['package_id']);
        $this->createIndex('product_id', '{{%basket}}', ['product_id']);
        $this->createIndex('blog_name', '{{%basket}}', ['blog_name']);
        $this->createIndex('customer_id', '{{%basket}}', ['customer_id']);

        $this->addForeignKey(
            'basket_ibfk_1',
            '{{%basket}}',
            ['product_id'],
            '{{%product}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'basket_ibfk_2',
            '{{%basket}}',
            ['blog_name'],
            '{{%blog}}',
            ['name'],
            'SET NULL',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%basket}}');
    }
}
