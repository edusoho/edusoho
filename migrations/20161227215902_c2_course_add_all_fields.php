<?php

use Phpmig\Migration\Migration;

class C2CourseAddRating extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `originPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '课程人民币原价';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `coinPrice` float(10,2) NOT NULL DEFAULT '0.00';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `originCoinPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '课程虚拟币原价';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `showStudentNumType` enum('opened','closed') NOT NULL DEFAULT 'opened' COMMENT '学员数显示模式';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `serializeMode` enum('none','serialize','finished') NOT NULL DEFAULT 'none' COMMENT '连载模式';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `giveCredit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学完课程所有课时，可获得的总学分';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `categoryId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类ID';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `about` text COMMENT '简介';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `recommended` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐课程';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `recommendedSeq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐序号';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `recommendedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `locationId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上课地区ID';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `address` varchar(255) NOT NULL DEFAULT '' COMMENT '上课地区地址';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '查看次数';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `discountId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '折扣活动ID';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `discount` float(10,2) NOT NULL DEFAULT '10.00' COMMENT '折扣';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `deadlineNotify` enum('active','none') NOT NULL DEFAULT 'none' COMMENT '开启有效期通知';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `daysOfNotifyBeforeDeadline` int(10) NOT NULL DEFAULT '0';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `useInClassroom` enum('single','more') NOT NULL DEFAULT 'single' COMMENT '课程能否用于多个班级';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `singleBuy` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '加入班级后课程能否单独购买';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `freeStartTime` int(10) NOT NULL DEFAULT '0';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `freeEndTime` int(10) NOT NULL DEFAULT '0';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `locked` int(10) NOT NULL DEFAULT '0' COMMENT '是否上锁1上锁,0解锁';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `maxRate` tinyint(3) unsigned NOT NULL DEFAULT '100' COMMENT '最大抵扣百分比';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `orgId` int(10) unsigned DEFAULT '1';");
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `orgCode` varchar(255) DEFAULT '1.' COMMENT '组织机构内部编码';");

    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `originPrice`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `coinPrice`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `originCoinPrice`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `showStudentNumType`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `serializeMode`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `giveCredit`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `categoryId`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `about`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `recommended`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `recommendedSeq`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `recommendedTime`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `locationId`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `address`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `hitNum`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `discountId`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `discount`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `deadlineNotify`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `daysOfNotifyBeforeDeadline`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `useInClassroom`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `singleBuy`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `freeStartTime`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `freeEndTime`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `locked`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `maxRate`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `orgId`;");
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `orgCode`;");
    }
}
