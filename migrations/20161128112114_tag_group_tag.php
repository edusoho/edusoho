<?php

use Phpmig\Migration\Migration;

class TagGroupTag extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
        CREATE TABLE IF NOT EXISTS `tag_group_tag` (
            `id` int(10) NOT NULL AUTO_INCREMENT,
            `tagId` int(10) NOT NULL DEFAULT '0' COMMENT '标签ID',
            `groupId` int(10) NOT NULL DEFAULT '0' COMMENT '标签组ID',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='标签组跟标签的中间表';
        ");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('DROP TABLE IF EXISTS `tag_group_tag`');
    }
}
