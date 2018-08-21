<?php

use Phpmig\Migration\Migration;

class AddTableLogV8 extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `log_v8` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统日志ID',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作人ID',
              `module` varchar(32) NOT NULL COMMENT '日志所属模块',
              `action` varchar(32) NOT NULL COMMENT '日志所属操作类型',
              `message` text NOT NULL COMMENT '日志内容',
              `data` text COMMENT '日志数据',
              `ip` varchar(255) NOT NULL COMMENT '日志记录IP',
              `browser` varchar(120) DEFAULT '' COMMENT '操作人浏览器信息',
              `operatingSystem` varchar(120) DEFAULT '' COMMENT '操作人操作系统',
              `device` varchar(120) DEFAULT '' COMMENT '操作人移动端或者计算机 移动端mobile 计算机computer',
              `userAgent` text COMMENT '操作人HTTP_USER_AGENT',
              `createdTime` int(10) unsigned NOT NULL COMMENT '日志发生时间',
              `level` char(10) NOT NULL COMMENT '日志等级',
              PRIMARY KEY (`id`),
              KEY `userId` (`userId`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
        ";

        $container = $this->getContainer();
        $container['db']->exec($sql);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('DROP TABLE IF EXISTS `log_v8`;');
    }
}
