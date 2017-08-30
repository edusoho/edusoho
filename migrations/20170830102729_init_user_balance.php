<?php

use Phpmig\Migration\Migration;

class InitUserBalance extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();

        $biz['db']->exec('INSERT INTO biz_user_balance(user_id) SELECT id FROM `user`');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();

        $biz['db']->exec('DELETE FROM `biz_user_balance`');
    }
}
