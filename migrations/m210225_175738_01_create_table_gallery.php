<?php

use yii\db\Migration;

class m210225_175738_01_create_table_gallery extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%gallery}}',
            [
                'name' => $this->string(16)->notNull()->append('PRIMARY KEY'),
                'updated_at' => $this->integer(),
                'width' => $this->integer()->notNull(),
                'height' => $this->integer()->notNull(),
                'type' => $this->string(12)->notNull(),
                'product_id' => $this->integer(),
                'user_name' => $this->string(31),
            ],
            $tableOptions
        );

        $this->createIndex('user_name', '{{%gallery}}', ['user_name']);
        $this->createIndex('product_id', '{{%gallery}}', ['product_id']);
    }

    public function down()
    {
        $this->dropTable('{{%gallery}}');
    }
}
