<?php

use Phpmig\Migration\Migration;

class S2b2cCourseV8Extend extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE `s2b2c_course_v8_extend` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `courseId` int(11) unsigned NOT NULL COMMENT '计划ID',
              `cooperationPrice` float(10,2) NOT NULL COMMENT '合作价格',
              `suggestionPrice` float(10,2) NOT NULL COMMENT '建议售价',
              `changelog` longtext,
              `createdTime` int(10) DEFAULT NULL,
              `updatedTime` int(10) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `courseId` (`courseId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('DROP TABLE  IF EXISTS `s2b2c_course_v8_extend`;');
    }
}
