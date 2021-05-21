<?php

use Phpmig\Migration\Migration;

class AddMultiClassTables extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE `multi_class_product` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `title` varchar(64) NOT NULL COMMENT '产品名称',
              `type` varchar(64) NOT NULL DEFAULT 'normal' COMMENT '产品类型',
              `remark` varchar(64) DEFAULT '' COMMENT '备注',
              `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
              `updatedTime` int(10) unsigned NOT NULL COMMENT '更新时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='班课产品';
        ");

        $biz['db']->exec("
            CREATE TABLE `multi_class` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `title` varchar(64) NOT NULL COMMENT '班课名称',
              `courseId` int(10) unsigned NOT NULL COMMENT '课程ID',
              `productId` int(10) unsigned NOT NULL COMMENT '产品ID',
              `copyId` int(10) unsigned default 0 COMMENT '复制来源ID',
              `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
              `updatedTime` int(10) unsigned NOT NULL COMMENT '更新时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='班课';
        ");

        $biz['db']->exec("ALTER TABLE `course_member` ADD `multiClassId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '班课ID' AFTER `courseId`;");
        $biz['db']->exec("ALTER TABLE `course_member` CHANGE `role` `role` enum('student','teacher', 'assistant') NOT NULL DEFAULT 'student' COMMENT '成员角色';");
        $biz['db']->exec("ALTER TABLE `course_task` ADD `multiClassId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '班课ID' AFTER `courseId`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('DROP TABLE `multi_class_product`;');
        $biz['db']->exec('DROP TABLE `multi_class`;');
        $biz['db']->exec('ALTER TABLE `course_member` DROP `multiClassId`;');
        $biz['db']->exec("ALTER TABLE `course_member` CHANGE `role` `role` enum('student','teacher') NOT NULL DEFAULT 'student' COMMENT '成员角色';");
        $biz['db']->exec('ALTER TABLE `course_task` DROP `multiClassId`;');
    }
}
