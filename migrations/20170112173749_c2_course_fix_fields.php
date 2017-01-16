<?php

use Phpmig\Migration\Migration;

class C2CourseFixFields extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `categoryId`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `orgId`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `orgCode`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `discountId`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `discount`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `hitNum`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `maxRate`;");

        $biz['db']->exec("ALTER TABLE `c2_course_set` ADD `orgId` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '组织机构ID';");
        $biz['db']->exec("ALTER TABLE `c2_course_set` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码';");
        $biz['db']->exec("ALTER TABLE `c2_course_set` ADD `discountId` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT '折扣活动ID'");
        $biz['db']->exec("ALTER TABLE `c2_course_set` ADD `discount` FLOAT( 10, 2 ) NOT NULL DEFAULT  '10' COMMENT  '折扣'");
        $biz['db']->exec("ALTER TABLE `c2_course_set` ADD `hitNum` int(10) unsigned NOT NULL DEFAULT  '0' COMMENT '分类ID'");
        $biz['db']->exec("ALTER TABLE `c2_course_set` ADD `maxRate` tinyint(3) unsigned NOT NULL DEFAULT '100' COMMENT '最大抵扣百分比'");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `c2_course_set` DROP COLUMN `orgId`;");
        $biz['db']->exec("ALTER TABLE `c2_course_set` DROP COLUMN `orgCode`;");
        $biz['db']->exec("ALTER TABLE `c2_course_set` DROP COLUMN `discountId`;");
        $biz['db']->exec("ALTER TABLE `c2_course_set` DROP COLUMN `discount`;");
        $biz['db']->exec("ALTER TABLE `c2_course_set` DROP COLUMN `hitNum`;");
        $biz['db']->exec("ALTER TABLE `c2_course_set` DROP COLUMN `maxRate`;");

        $biz['db']->exec("ALTER TABLE `c2_course` ADD `categoryId` int(10) unsigned NOT NULL DEFAULT  '0' COMMENT  '分类ID'");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `orgId` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '组织机构ID';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `discountId` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '折扣活动ID'");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `discount` FLOAT( 10, 2 ) NOT NULL DEFAULT  '10' COMMENT  '折扣'");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `hitNum` int(10) unsigned NOT NULL DEFAULT  '0' COMMENT  '分类ID'");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `maxRate` tinyint(3) unsigned NOT NULL DEFAULT '100' COMMENT '最大抵扣百分比'");
    }
}
