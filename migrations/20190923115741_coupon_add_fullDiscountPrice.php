<?php

use Phpmig\Migration\Migration;

class CouponAddFullDiscountPrice extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $db = $this->getContainer()->offsetGet('db');
        $db->exec('ALTER TABLE `coupon` ADD `fullDiscountPrice` float(10,2) unsigned NULL;');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $db = $this->getContainer()->offsetGet('db');
        $db->exec('ALTER TABLE `coupon` DROP COLUMN `fullDiscountPrice`');
    }
}
