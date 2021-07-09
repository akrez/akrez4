<?php

use yii\db\Migration;

class m210709_131918_007_create_table_customer extends Migration
{
    public function up()
    {
        $tableName = $this->db->tablePrefix . 'customer';
        if ($this->db->getTableSchema($tableName, true) !== null) {
            return;
        }
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%customer}}',
            [
                'id' => $this->primaryKey(),
                'updated_at' => $this->integer()->unsigned(),
                'created_at' => $this->integer()->unsigned(),
                'status' => $this->tinyInteger(4)->notNull(),
                'token' => $this->string(32),
                'password_hash' => $this->string(),
                'verify_token' => $this->string(11),
                'verify_at' => $this->integer(),
                'reset_token' => $this->string(11),
                'reset_at' => $this->integer()->unsigned(),
                'mobile' => $this->string(15),
                'name' => $this->string(60),
                'params' => $this->text(),
                'blog_name' => $this->string(60)->notNull(),
            ],
            $tableOptions
        );

        $this->createIndex('blog_name', '{{%customer}}', ['blog_name']);

        $this->addForeignKey(
            'customer_ibfk_1',
            '{{%customer}}',
            ['blog_name'],
            '{{%blog}}',
            ['name'],
            'NO ACTION',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%customer}}');
    }
}
