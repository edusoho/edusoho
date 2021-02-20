<?php

use Phpmig\Migration\Migration;

class ActivityTrainingTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE `activity_training` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `createdTime` int(10) NOT NULL,
                `createdUserId` int(11) NOT NULL,
                `updatedTime` int(11) NOT NULL,
                `syncId` int(10) NOT NULL DEFAULT '0',
                `link_url` varchar(100) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            DROP TABLE IF EXISTS `activity_training`;
        ');
    }
}
