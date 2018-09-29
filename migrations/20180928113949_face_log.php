<?php

use Phpmig\Migration\Migration;

class FaceLog extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE IF NOT EXISTS `face_log` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `userId` int(10) unsigned NOT NULL DEFAULT '0',
                `status` varchar(32) NOT NULL DEFAULT '',
                `createdTime` int(10) NOT NULL DEFAULT '0',
                `sessionId` varchar(64) NOT NULL DEFAULT '' COMMENT '人脸识别sessionId',
                PRIMARY KEY (`id`),
                KEY `idx_userId_status_createdTime` (`userId`,`createdTime`,`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            DROP TABLE IF EXISTS `face_log`;
        ");
    }
}
