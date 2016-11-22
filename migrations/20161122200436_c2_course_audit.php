<?php

use Phpmig\Migration\Migration;

class C2CourseAudit extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
            CREATE TABLE `c2_course_audit` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `courseSetId` int(11) DEFAULT NULL,
              `courseId` int(11) DEFAULT NULL,
              `status` varchar(32) DEFAULT NULL,
              `remark` varchar(1024) DEFAULT NULL,
              `creator` int(11) DEFAULT NULL,
              `created` int(11) DEFAULT NULL,
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
        $db->exec("
            DROP TABLE IF EXISTS `c2_course_audit`
        ");
    }
}
