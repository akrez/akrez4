<?php

use yii\db\Migration;

class m210225_175739_07_create_table_package extends Migration
{
    public function up()
    {
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
                'product_id' => $this->integer()->notNull(),
                'user_name' => $this->string(31),
                'params' => $this->text(),
            ],
            $tableOptions
        );

        $this->createIndex('product_id', '{{%package}}', ['product_id']);
        $this->createIndex('user_name', '{{%package}}', ['user_name']);

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
            ['user_name'],
            '{{%user}}',
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
