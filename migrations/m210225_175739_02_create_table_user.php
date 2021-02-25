<?php

use yii\db\Migration;

class m210225_175739_02_create_table_user extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%user}}',
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
                'params' => $this->text()->comment('{ "address":"", "phone":"", "mobile":"", "instagram":"", "telegram":"", "facebook":"", "twitter":"", "slug":"", "des":"" }'),
            ],
            $tableOptions
        );

        $this->createIndex('email', '{{%user}}', ['email'], true);
        $this->createIndex('logo', '{{%user}}', ['logo']);

        $this->addForeignKey(
            'user_ibfk_1',
            '{{%user}}',
            ['logo'],
            '{{%gallery}}',
            ['name'],
            'NO ACTION',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%user}}');
    }
}
