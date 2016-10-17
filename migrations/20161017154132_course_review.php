<?php

use Phpmig\Migration\Migration;

class CourseReview extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $connection = $container['db'];

        if (!$this->isFieldExist('course', 'expiryMode')) {
            $connection->exec("ALTER TABLE  `course` ADD COLUMN   `expiryMode` ENUM('date','days','none') NOT NULL DEFAULT 'none' COMMENT '有效期模式（截止日期|有效期天数|不设置）' AFTER `originCoinPrice`");
            $connection->exec("UPDATE `course` SET  expiryMode = 'days' WHERE `expiryDay` > 0");
        }

        if (!$this->isFieldExist('course_review', 'parentId')) {
            $connection->exec("ALTER TABLE course_review ADD `parentId` int(10) NOT NULL DEFAULT '0 'COMMENT '回复ID';");
        }

        if (!$this->isFieldExist('course_review', 'updatedTime')) {
            $connection->exec("ALTER TABLE course_review ADD `updatedTime` int(10) ;");
        }

        if (!$this->isFieldExist('classroom_review', 'parentId')) {
            $connection->exec("ALTER TABLE classroom_review ADD `parentId` int(10) NOT NULL DEFAULT '0' COMMENT '回复ID';");
        }

        if (!$this->isFieldExist('classroom_review', 'updatedTime')) {
            $connection->exec("ALTER TABLE classroom_review ADD `updatedTime` int(10) ;");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }

    protected function isFieldExist($table, $filedName)
    {
        $sql    = "DESCRIBE `{$table}` `{$filedName}`;";
        $container = $this->getContainer();
        $connection = $container['db'];
        $result = $connection->fetchAssoc($sql);
        return empty($result) ? false : true;
    }
}
