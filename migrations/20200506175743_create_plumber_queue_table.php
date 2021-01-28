<?php

use Phpmig\Migration\Migration;

class CreatePlumberQueueTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getContainer()->offsetGet('db')->exec("
             CREATE TABLE  IF NOT EXISTS  `plumber_queue`(
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `worker` VARCHAR(64) NOT NULL COMMENT 'workerTopic',
                `jobId` VARCHAR(64) NOT NULL COMMENT 'jobId',
                `body` TEXT NOT NULL COMMENT 'Job消息主体', 
                `status` VARCHAR(32) NOT NULL DEFAULT 'acquired' COMMENT 'Job执行状态', 
                `priority` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '优先级', 
                `trace` LONGTEXT DEFAULT NULL COMMENT '异常信息', 
                `createdTime` INT(10) UNSIGNED NOT NULL DEFAULT 0, 
                `cupdatedTime` INT(10) UNSIGNED NOT NULL DEFAULT 0, 
                PRIMARY KEY (`id`)
             ) ENGINE = InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getContainer()->offsetGet('db')->exec('DROP TABLE IF EXISTS `plumber_queue`');
    }
}
