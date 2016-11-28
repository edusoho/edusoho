<?php

use Phpmig\Migration\Migration;

class C2CourseSet extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("CREATE TABLE `c2_course_set` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `type` varchar(32) NOT NULL DEFAULT '',
            `title` varchar(1024) DEFAULT '',
            `subtitle` varchar(1024) DEFAULT '',
            `tags` text,
            `categories` text,
            `smallPicture` varchar(255) DEFAULT NULL,
            `middlePicture` varchar(255) DEFAULT NULL,
            `largePicture` varchar(255) DEFAULT NULL,
            `status` varchar(32) DEFAULT '0' COMMENT 'draft, published, closed',
            `creator` int(11) DEFAULT '0',
            `created` int(11) DEFAULT '0',
            `updated` int(11) DEFAULT '0',
            `deleted` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("DROP TABLE IF EXISTS `c2_course_set`");
    }
}
