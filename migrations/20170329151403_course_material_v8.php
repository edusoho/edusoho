<?php

use Phpmig\Migration\Migration;

class CourseMaterialV8 extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
          CREATE TABLE `course_material_v8` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程资料ID',
            `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料所属课程ID',
            `lessonId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料所属课时ID',
            `title` varchar(1024) NOT NULL COMMENT '资料标题',
            `description` text COMMENT '资料描述',
            `link` varchar(1024) NOT NULL DEFAULT '' COMMENT '外部链接地址',
            `fileId` int(10) unsigned NOT NULL COMMENT '资料文件ID',
            `fileUri` varchar(255) NOT NULL DEFAULT '' COMMENT '资料文件URI',
            `fileMime` varchar(255) NOT NULL DEFAULT '' COMMENT '资料文件MIME',
            `fileSize` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料文件大小',
            `source` varchar(50) NOT NULL DEFAULT 'coursematerial',
            `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料创建人ID',
            `createdTime` int(10) unsigned NOT NULL COMMENT '资料创建时间',
            `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制的资料Id',
            `type` varchar(50) NOT NULL DEFAULT 'course' COMMENT '课程类型',
            `courseSetId` int(10) DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
        ");

        $db->exec('INSERT INTO `course_material_v8`  SELECT * FROM `course_material` WHERE  id NOT IN (SELECT id FROM  `course_material_v8`);');
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('
           Drop table `course_material_v8`;
        ');
    }
}
