<?php

use yii\db\Migration;

class m210709_131917_003_create_table_log_api extends Migration
{
    public function up()
    {
        $tableName = $this->db->tablePrefix . 'log_api';
        if ($this->db->getTableSchema($tableName, true) !== null) {
            return;
        }
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%log_api}}',
            [
                'id' => $this->primaryKey(),
                'blog_name' => $this->string(60),
                'ip' => $this->string(60),
                'method' => $this->string(11),
                'is_ajax' => $this->boolean(),
                'url' => $this->string(2047),
                'response_http_code' => $this->integer(),
                'created_date' => $this->string(19),
                'data_post' => $this->string(4096),
                'user_agent' => $this->string(2047),
                'controller' => $this->string(60),
                'action' => $this->string(60),
                'model_id' => $this->string(60),
                'customer_id' => $this->integer(),
                'model_category_id' => $this->integer(),
                'model_parent_id' => $this->string(60),
            ],
            $tableOptions
        );
    }

    public function down()
    {
        $this->dropTable('{{%log_api}}');
    }
}
