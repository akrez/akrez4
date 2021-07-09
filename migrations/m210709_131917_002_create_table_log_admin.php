<?php

use yii\db\Migration;

class m210709_131917_002_create_table_log_admin extends Migration
{
    public function up()
    {
        $tableName = $this->db->tablePrefix . 'log_admin';
        if ($this->db->getTableSchema($tableName, true) !== null) {
            return;
        }
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%log_admin}}',
            [
                'id' => $this->primaryKey(),
                'blog_name' => $this->string(60),
                'ip' => $this->string(60),
                'method' => $this->string(11),
                'is_ajax' => $this->boolean(),
                'url' => $this->string(2047),
                'response_http_code' => $this->integer(),
                'created_date' => $this->string(19),
                'data_post' => $this->text(),
                'user_agent' => $this->string(2047),
                'controller' => $this->string(60),
                'action' => $this->string(60),
                'model_id' => $this->string(60),
                'model_parent_id' => $this->string(60),
            ],
            $tableOptions
        );
    }

    public function down()
    {
        $this->dropTable('{{%log_admin}}');
    }
}
