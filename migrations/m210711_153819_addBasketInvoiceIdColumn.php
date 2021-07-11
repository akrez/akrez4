<?php

use yii\db\Migration;

/**
 * Class m210711_153819_addBasketInvoiceIdColumn
 */
class m210711_153819_addBasketInvoiceIdColumn extends Migration
{
    public function up()
    {
        $this->addColumn('{{%basket}}', 'invoice_id', $this->integer());
    }

    public function down()
    {
        echo "m210711_153819_addBasketInvoiceIdColumn cannot be reverted.\n";

        return false;
    }
}
