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
                'created_at' => $this->integer(),
                'price' => $this->double()->notNull(),
                'cnt' => $this->integer()->notNull(),
                'package_id' => $this->integer(),
                'customer_id' => $this->integer(),
                'blog_name' => $this->string(31),
            ],
            $tableOptions
        );

        $this->createIndex('package_id', '{{%basket}}', ['package_id']);
        $this->createIndex('blog_name', '{{%basket}}', ['blog_name']);
        $this->createIndex('customer_id', '{{%basket}}', ['customer_id']);

        $this->addForeignKey(
            'basket_ibfk_3',
            '{{%basket}}',
            ['package_id'],
            '{{%package}}',
            ['id'],
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'basket_ibfk_4',
            '{{%basket}}',
            ['customer_id'],
            '{{%customer}}',
            ['id'],
            'SET NULL',
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
