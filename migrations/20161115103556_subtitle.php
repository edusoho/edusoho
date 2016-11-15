<?php

use Phpmig\Migration\Migration;

class Subtitle extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        if (!$this->isFieldExist('course_member', 'finishedTime')) {
            $db->exec("ALTER TABLE `course_member` ADD `finishedTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '完成课程时间' AFTER `isLearned`");
        }

        $now = time();
        $db->exec("UPDATE `course_member` SET finishedTime = {$now} WHERE isLearned = 1");

        $db->exec("CREATE TABLE IF NOT EXISTS `subtitle` (
                `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL COMMENT '字幕名称',
                `subtitleId` int(10) UNSIGNED NOT NULL COMMENT 'subtitle的uploadFileId',
                `mediaId` int(10) UNSIGNED NOT NULL COMMENT 'video/audio的uploadFileId',
                `ext` varchar(12) NOT NULL DEFAULT '' COMMENT '后缀',
                `createdTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`)
            ) COMMENT='字幕关联表';");

        if ($this->isFieldExist('upload_files', 'type')) {
            $db->exec("ALTER TABLE `upload_files` CHANGE COLUMN `type` `type` enum('document','video','audio','image','ppt','other','flash','subtitle') NOT NULL DEFAULT 'other' COMMENT '文件类型';");
        }

        if ($this->isFieldExist('upload_file_inits', 'type')) {
            $db->exec("ALTER TABLE `upload_file_inits` CHANGE COLUMN `type` `type` enum('document','video','audio','image','ppt','other','flash','subtitle') NOT NULL DEFAULT 'other' COMMENT '文件类型';");
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
        $biz = $this->getContainer();
        $db = $biz['db'];
        $result = $db->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

}
