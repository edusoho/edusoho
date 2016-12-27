<?php

use Phpmig\Migration\Migration;

class C2RemoveUnusedTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            DROP TABLE `c2_course_audit`;
            DROP TABLE `c2_course_marketing`;
            DROP TABLE `c2_course_statistics`;
        ");

    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE `c2_course_audit` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `courseSetId` int(11) DEFAULT NULL,
              `courseId` int(11) DEFAULT NULL,
              `status` varchar(32) DEFAULT NULL,
              `remark` varchar(1024) DEFAULT NULL,
              `creator` int(11) DEFAULT NULL,
              `created` int(11) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE `c2_course_marketing` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `courseSetId` int(11) DEFAULT NULL,
              `courseId` int(11) DEFAULT NULL,
              `isFree` tinyint(4) DEFAULT NULL,
              `price` int(11) DEFAULT NULL,
              `memberRule` varchar(1024) DEFAULT NULL,
              `joinMode` tinyint(4) DEFAULT NULL,
              `enableTrylook` tinyint(1) DEFAULT NULL,
              `trylookLength` int(11) DEFAULT NULL,
              `trylookLimit` int(11) DEFAULT NULL,
              `services` int(11) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


            CREATE TABLE `c2_course_statistics` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `courseSetId` int(11) DEFAULT NULL,
              `courseId` int(11) DEFAULT NULL,
              `studentNum` int(11) DEFAULT NULL,
              `hitNum` int(11) DEFAULT NULL,
              `noteNum` int(11) DEFAULT NULL,
              `reviewNum` int(11) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

    }
}
