<?php

use yii\db\Migration;

class m210709_131918_013_create_table_package extends Migration
{
    public function up()
    {
        $tableName = $this->db->tablePrefix . 'package';
        if ($this->db->getTableSchema($tableName, true) !== null) {
            return;
        }
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%package}}',
            [
                'id' => $this->primaryKey(),
                'updated_at' => $this->integer(),
                'status' => $this->string(12)->notNull(),
                'price' => $this->double()->notNull(),
                'cache_stock' => $this->integer()->defaultValue('0'),
                'color_code' => $this->string(31),
                'product_id' => $this->integer()->notNull(),
                'blog_name' => $this->string(31),
                'params' => $this->text(),
            ],
            $tableOptions
        );

        $this->createIndex('product_id', '{{%package}}', ['product_id']);
        $this->createIndex('blog_name', '{{%package}}', ['blog_name']);

        $this->addForeignKey(
            'package_ibfk_1',
            '{{%package}}',
            ['product_id'],
            '{{%product}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'package_ibfk_2',
            '{{%package}}',
            ['blog_name'],
            '{{%blog}}',
            ['name'],
            'SET NULL',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%package}}');
    }
}
