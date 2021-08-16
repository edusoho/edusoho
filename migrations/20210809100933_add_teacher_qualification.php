<?php

use Phpmig\Migration\Migration;

class AddTeacherQualification extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE IF NOT EXISTS `teacher_qualification` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		        `user_id` int(10) unsigned NOT NULL DEFAULT '0',
		        `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '教师资质照片',
                `avatarFileId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件id',
		        `code` varchar(64) NOT NULL DEFAULT '' COMMENT '教师资质编号',
		        `created_time` int(10) unsigned NOT NULL DEFAULT 0,
                `updated_time` int(10) unsigned NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`)
		    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            DROP TABLE `teacher_qualification`;
        ');
    }
}
