<?php

use Phpmig\Migration\Migration;

class DocActivity extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
        CREATE TABLE IF NOT EXISTS `doc_activity` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `mediaId` int(11) NOT NULL,
          `finishType` varchar(32) NOT NULL DEFAULT '' COMMENT 'click, detail',
          `finishDetail` varchar(32) DEFAULT '0' COMMENT '至少观看X分钟',
          `createdTime` int(10) NOT NULL,
          `createdUserId` int(11) NOT NULL,
          `updatedTime` int(11) DEFAULT NULL,
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
        $db->exec('DROP TABLE IF EXISTS `doc_activity`');
    }
}
