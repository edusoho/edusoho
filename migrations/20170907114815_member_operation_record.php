<?php

use Phpmig\Migration\Migration;

class MemberOperationRecord extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec("CREATE TABLE `member_operation_record` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `member_id` int(10) unsigned NOT NULL COMMENT '成员ID',
                  `target_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '类型ID',
                  `target_type` varchar(32) NOT NULL DEFAULT '' COMMENT '类型（classroom, course）',
                  `operate_type` varchar(32) NOT NULL DEFAULT '' COMMENT '操作类型（join, exit）',
                  `operate_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作时间',
                  `operator_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作用户ID',
                  `data` text COMMENT 'extra data',
                  `created_time` int(10) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('DROP TABLE IF EXISTS `member_operation_record`');
    }
}
