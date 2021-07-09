<?php

use yii\db\Migration;

/**
 * Class m210709_133822_removeBlogComment
 */
class m210709_133822_removeBlogComment extends Migration
{
    public function up()
    {
        $this->dropCommentFromColumn('{{%blog}}', 'params');
    }

    public function down()
    {
        echo "m210709_133822_removeBlogComment cannot be reverted.\n";

        return false;
    }
}
