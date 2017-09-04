<?php

use Phpmig\Migration\Migration;

class CouponAddUsingStatus extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();

        $db = $biz['db'];

        $db->exec('ALTER TABLE `coupon` CHANGE `status` `status` ENUM(\'used\',\'unused\',\'receive\',\'using\') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT \'unused\';');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
