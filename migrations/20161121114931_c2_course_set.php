<?php

use Phpmig\Migration\Migration;

class C2CourseSet extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("CREATE TABLE `c2_course_set` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `type` varchar(32) NOT NULL DEFAULT '',
            `title` varchar(1024) DEFAULT '',
            `subtitle` varchar(1024) DEFAULT '',
            `tags` text,
            `categoryId` int(10) NOT NULL DEFAULT '0',
            `serializeMode` varchar(32) NOT NULL DEFAULT 'none' COMMENT 'none, serilized, finished',
            `status` varchar(32) DEFAULT '0' COMMENT 'draft, published, closed',
            `summary` TEXT,
            `goals` TEXT,
            `audiences` TEXT,
            `cover` VARCHAR(1024),
            `ratingNum` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程评论数',
            `rating` float UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程评分',
            `noteNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程笔记数',
            `studentNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程学员数',
            `recommended` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐课程',
            `recommendedSeq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐序号',
            `recommendedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间',
            `orgId` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '组织机构ID',
            `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码',
            `discountId` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT '折扣活动ID',
            `discount` FLOAT( 10, 2 ) NOT NULL DEFAULT  '10' COMMENT  '折扣',
            `hitNum` int(10) unsigned NOT NULL DEFAULT  '0' COMMENT '课程点击数',
            `maxRate` tinyint(3) unsigned NOT NULL DEFAULT '100' COMMENT '最大抵扣百分比',
            `materialNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传的资料数量',
            `parentId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '是否班级课程',
            `locked` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '是否锁住',
            `maxCoursePrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '已发布教学计划的最高价格',
            `minCoursePrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '已发布教学计划的最低价格',
            `teacherIds` varchar(1024) DEFAULT null,
            `creator` int(11) DEFAULT '0',
            `createdTime` INT(11) unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
            `updatedTime` INT(11) unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('DROP TABLE IF EXISTS `c2_course_set`');
    }
}
