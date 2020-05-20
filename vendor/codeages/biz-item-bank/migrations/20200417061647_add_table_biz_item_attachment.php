<?php

use Phpmig\Migration\Migration;

class AddTableBizItemAttachment extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getContainer()->offsetGet('db')->exec("
            CREATE TABLE `biz_item_attachment` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `global_id` varchar(32) NOT NULL DEFAULT '' COMMENT '云文件ID',
                `hash_id` varchar(128) NOT NULL DEFAULT '' COMMENT '文件的HashID',
                `target_id` int(10) NOT NULL DEFAULT '0' COMMENT '对象id',
                `target_type` varchar(32) NOT NULL DEFAULT '' COMMENT '对象类型', 
                `module` varchar(32) NOT NULL DEFAULT '' COMMENT '附件所属题目模块',
                `file_name` varchar(1024) NOT NULL DEFAULT '' COMMENT '附件名',
                `ext` varchar(12) NOT NULL DEFAULT '' COMMENT '后缀',
                `size` int(10) NOT NULL DEFAULT '0' COMMENT '文件大小',
                `status` varchar(32) NOT NULL DEFAULT '' COMMENT '上传状态',
                `file_type` varchar(32) NOT NULL DEFAULT '' COMMENT '文件类型',
                `created_user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户Id',
                `convert_status` varchar(32) NOT NULL DEFAULT 'none' COMMENT '转码状态',
                `audio_convert_status` varchar(32) NOT NULL DEFAULT 'none' COMMENT '转音频状态',
                `mp4_convert_status` varchar(32) NOT NULL DEFAULT 'none' COMMENT '转mp4状态',
                `updated_time` int(10) NOT NULL DEFAULT '0',
                `created_time` int(10) NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                KEY `target_id` (`target_id`),
                KEY `target_type` (`target_type`),
                KEY `global_id` (`global_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='题目附件表';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getContainer()->offsetGet('db')->exec('
            DROP TABLE IF EXISTS `biz_item_attachment`;
        ');
    }
}
