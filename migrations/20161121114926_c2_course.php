<?php

use Phpmig\Migration\Migration;

class C2Course extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("CREATE TABLE `c2_course` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `courseSetId` int(11) NOT NULL,
            `copyCourseId` int(11) DEFAULT NULL,
            `title` varchar(1024) DEFAULT NULL,
            `learnMode` varchar(32) DEFAULT NULL COMMENT 'byOrder, freeOrder',
            `expiryMode` varchar(32) DEFAULT NULL,
            `expiryDay` int(11) DEFAULT NULL,
            `summary` text,
            `goals` text,
            `audiences` text,
            `maxStudentNum` int(11) DEFAULT '0',
            `smallPicture` varchar(255) DEFAULT NULL,
            `middlePicture` varchar(255) DEFAULT NULL,
            `largePicture` varchar(255) DEFAULT NULL,
            `status` varchar(32) DEFAULT NULL COMMENT 'draft, published, closed',
            `auditStatus` varchar(32) DEFAULT NULL COMMENT 'draft, committed, rejected, accepted',
            `auditRemark` text,
            `creator` int(11) DEFAULT NULL,
            `created` int(11) DEFAULT NULL,
            `updated` int(11) DEFAULT NULL,
            `deleted` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("DROP TABLE IF EXISTS `c2_course`");
    }
}
