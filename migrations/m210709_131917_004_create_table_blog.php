<?php

use yii\db\Migration;

class m210709_131917_004_create_table_blog extends Migration
{
    public function up()
    {
        $tableName = $this->db->tablePrefix . 'blog';
        if ($this->db->getTableSchema($tableName, true) !== null) {
            return;
        }
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%blog}}',
            [
                'name' => $this->string(60)->notNull()->append('PRIMARY KEY'),
                'updated_at' => $this->integer(10)->unsigned(),
                'created_at' => $this->integer(10)->unsigned(),
                'status' => $this->tinyInteger(4)->notNull(),
                'title' => $this->string(60),
                'logo' => $this->string(16),
                'token' => $this->string(32),
                'password_hash' => $this->string(),
                'verify_token' => $this->string(11),
                'verify_at' => $this->integer()->unsigned(),
                'reset_token' => $this->string(11),
                'reset_at' => $this->integer()->unsigned(),
                'email' => $this->string(),
                'mobile' => $this->string(15),
                'language' => $this->string(8),
                'telegram_bot_token' => $this->string(63),
                'params' => $this->text()->comment('{ "address":"", "phone":"", "mobile":"", "instagram":"", "telegram":"", "facebook":"", "twitter":"", "slug":"", "des":"" }'),
            ],
            $tableOptions
        );

        $this->createIndex('logo', '{{%blog}}', ['logo']);
        $this->createIndex('mobile', '{{%blog}}', ['mobile']);
        $this->createIndex('email', '{{%blog}}', ['email'], true);

        $this->addForeignKey(
            'blog_ibfk_1',
            '{{%blog}}',
            ['logo'],
            '{{%gallery}}',
            ['name'],
            'SET NULL',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%blog}}');
    }
}
