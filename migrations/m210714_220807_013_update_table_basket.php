<?php

use yii\db\Migration;

class m210714_220807_013_update_table_basket extends Migration
{
    public function up()
    {
        $this->addForeignKey(
            'basket_ibfk_2',
            '{{%basket}}',
            ['customer_id'],
            '{{%customer}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'basket_ibfk_3',
            '{{%basket}}',
            ['package_id'],
            '{{%package}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'basket_ibfk_1',
            '{{%basket}}',
            ['blog_name'],
            '{{%blog}}',
            ['name'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropForeignKey('basket_ibfk_2', '{{%basket}}');
        $this->dropForeignKey('basket_ibfk_3', '{{%basket}}');
        $this->dropForeignKey('basket_ibfk_1', '{{%basket}}');
    }
}
