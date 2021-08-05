<?php

use Phpmig\Migration\Migration;

class AddMultiClassGroup extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE IF NOT EXISTS `multi_class_group` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(64) NOT NULL COMMENT '分组名称',
                `assistant_id` int(10) unsigned NOT NULL COMMENT '助教ID',
                `multi_class_id` int(10) unsigned NOT NULL COMMENT '班课ID',
                `course_id` int(10) unsigned NOT NULL default 0 COMMENT '课程ID',
                `student_num` int(10) unsigned NOT NULL default 0 COMMENT '学员数量',
                `created_time` int(10) unsigned NOT NULL COMMENT '创建时间',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='班课分组';

            CREATE TABLE IF NOT EXISTS `multi_class_live_group` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `group_id` int(10) unsigned NOT NULL COMMENT '分组ID',
                `live_code` varchar(64) NOT NULL default '' COMMENT '直播分组Code',
                `live_id` int(10) unsigned NOT NULL default 0 COMMENT '直播ID',
                `created_time` int(10) unsigned NOT NULL COMMENT '创建时间',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='班课分组';   

            CREATE TABLE IF NOT EXISTS `multi_class_record` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` int(10) unsigned NOT NULL COMMENT '用户ID',
                `multi_class_id` int(10) NOT NULL default  '班课ID',
                `data` text default '' COMMENT 'json格式信息',
                `sign` varchar(64) not null default '' COMMENT '唯一标识',
                `created_time` int(10) unsigned NOT NULL COMMENT '创建时间',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='班课上报记录'; 
        ");

        $biz['db']->exec("
            ALTER TABLE `assistant_student` ADD COLUMN `group_id` int(10) not null default 0 COMMENT '分组ID' after `multiClassId`;
            ALTER TABLE `multi_class` ADD COLUMN `start_time` int(10) not null default '0' COMMENT '开课时间' after `productId`;
            ALTER TABLE `multi_class` ADD COLUMN `end_time` int(10) not null default '0' COMMENT '结课时间' after `start_time`;
            ALTER TABLE `multi_class` ADD COLUMN `type` varchar(32) not null default 'normal' COMMENT '班课或者分组班课(normal, group)' after `end_time`;
            ALTER TABLE `multi_class` ADD COLUMN `service_setting_type` varchar(32) not null default 'default' COMMENT '助教服务人数设置类型(default, custom)' after `type`;
            ALTER TABLE `multi_class` ADD COLUMN `service_num` int(10) unsigned not null default 0 COMMENT '助教服务人数' after `service_setting_type`;
            ALTER TABLE `multi_class` ADD COLUMN `group_limit_num` int(10) unsigned not null default 0 COMMENT '分组人数限制' after `service_num`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            DROP TABLE `multi_class_group`;
            DROP TABLE `multi_class_group_live`;
            DROP TABLE `multi_class_record`;
        ');
        $biz['db']->exec('
            ALTER TABLE `assistant_student` DROP COLUMN `group_id`;
            ALTER TABLE `multi_class` DROP COLUMN `status`;
            ALTER TABLE `multi_class` DROP COLUMN `type`;
            ALTER TABLE `multi_class` DROP COLUMN `service_setting_type`;
            ALTER TABLE `multi_class` DROP COLUMN `service_num`;    
            ALTER TABLE `multi_class` DROP COLUMN `group_limit_num`;
        ');
    }
}
