<?php

use Phpmig\Migration\Migration;

class CourseReview extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $container = $this->getContainer();
        $connection = $container['db'];

        $connection->exec("ALTER TABLE  `course` ADD COLUMN `expiryMode` ENUM('date','days','none') NOT NULL DEFAULT 'none' COMMENT '有效期模式（截止日期|有效期天数|不设置）' AFTER `originCoinPrice`");
        $connection->exec("UPDATE `course` SET  expiryMode = 'days' WHERE `expiryDay` > 0");
        $connection->exec("ALTER TABLE course_review ADD `parentId` int(10) NOT NULL DEFAULT '0 'COMMENT '回复ID';");
        $connection->exec('ALTER TABLE course_review ADD `updatedTime` int(10) ;');
        $connection->exec("ALTER TABLE classroom_review ADD `parentId` int(10) NOT NULL DEFAULT '0' COMMENT '回复ID';");
        $connection->exec('ALTER TABLE classroom_review ADD `updatedTime` int(10) ;');
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec('ALTER TABLE `course` DROP COLUMN `expiryMode`');
        $db->exec('ALTER TABLE `course_review` DROP COLUMN `parentId`');
        $db->exec('ALTER TABLE `course_review` DROP COLUMN `updatedTime`');
        $db->exec('ALTER TABLE `classroom_review` DROP COLUMN `parentId`');
        $db->exec('ALTER TABLE `classroom_review` DROP COLUMN `updatedTime`');
    }
}
