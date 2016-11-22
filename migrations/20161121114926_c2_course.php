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
              `title` varchar(1024) DEFAULT NULL,
              `learnMode` varchar(32) DEFAULT NULL COMMENT 'byOrder, freeOrder',
              `expiryMode` varchar(32) DEFAULT NULL,
              `expiryDays` int(11) DEFAULT NULL,
              `expiryStartDate` int(11) DEFAULT NULL,
              `expiryEndDate` int(11) DEFAULT NULL,
              `summary` text,
              `goals` text,
              `audiences` text,
              `isDefault` tinyint(1) DEFAULT '0',
              `maxStudentNum` int(11) DEFAULT '0',
              `status` varchar(32) DEFAULT NULL COMMENT 'draft, published, closed',
              `auditStatus` varchar(32) DEFAULT NULL COMMENT 'draft, committed, rejected, accepted',
              `auditRemark` text,
              `creator` int(11) DEFAULT NULL,
              `created` int(11) DEFAULT NULL,
              `updated` int(11) DEFAULT NULL,
              `deleted` int(11) NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
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
