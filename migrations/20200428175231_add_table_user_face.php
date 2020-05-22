<?php

use Phpmig\Migration\Migration;

class AddTableUserFace extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
            CREATE TABLE IF NOT EXISTS `user_face` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
            `picture` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '文件路径',
            `capture_code` varchar (20) NOT NULL DEFAULT '' COMMENT '采集头像时链接码',
            `created_time` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
            `updated_time` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='云监考头像采集';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('
             DROP TABLE `user_face`
        ');
    }
}
