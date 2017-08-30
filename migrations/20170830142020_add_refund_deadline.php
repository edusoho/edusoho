<?php

use Phpmig\Migration\Migration;

class AddRefundDeadline extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();

        $db = $biz['db'];

        $db->exec("ALTER TABLE `course_member` ADD COLUMN `refundDeadline`  int(10) NOT NULL DEFAULT '0' COMMENT '退款截止时间' AFTER `deadline`");

        $db->exec("ALTER TABLE `classroom_member` ADD COLUMN `refundDeadline`  int(10) NOT NULL DEFAULT '0' COMMENT '退款截止时间' AFTER `deadline`");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();

        $db = $biz['db'];

        $db->exec('ALTER TABLE `classroom_member` DROP COLUMN `refundEndTime`;');

        $db->exec('ALTER TABLE `course_member` DROP COLUMN `refundEndTime`;');
    }
}
