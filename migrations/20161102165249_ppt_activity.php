<?php

use Phpmig\Migration\Migration;

class PptActivity extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
        CREATE TABLE IF NOT EXISTS `ppt_activity` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `mediaId` int(11) NOT NULL,
          `finishType` varchar(32) NOT NULL DEFAULT '' COMMENT 'end, time',
          `finishDetail` varchar(32) DEFAULT '0' COMMENT '至少观看X分钟',
          `createdTime` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '',
          `createdUserId` int(11) NOT NULL,
          `updatedTime` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '',
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
        $db->exec('DROP TABLE IF EXISTS `ppt_activity`');
    }
}
