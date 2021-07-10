<?php

use yii\db\Migration;

/**
 * Class m210710_164750_addBasketrelation
 */
class m210710_164750_addBasketrelation extends Migration
{
    public function up()
    {
        $this->addForeignKey(
            'basket_ibfk_3',
            '{{%basket}}',
            ['package_id'],
            '{{%package}}',
            ['id'],
            'NO ACTION',
            'CASCADE'
        );
        $this->addForeignKey(
            'basket_ibfk_4',
            '{{%basket}}',
            ['customer_id'],
            '{{%customer}}',
            ['id'],
            'NO ACTION',
            'CASCADE'
        );
    }

    public function down()
    {
        echo "m210710_164750_addBasketrelation cannot be reverted.\n";

        return false;
    }
}
