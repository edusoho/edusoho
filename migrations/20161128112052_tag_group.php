<?php

use Phpmig\Migration\Migration;

class TagGroup extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
             CREATE TABLE IF NOT EXISTS `tag_group` (
                `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '标签ID',
                `name` varchar(255) NOT NULL DEFAULT '' COMMENT '标签组名字',
                `scope` varchar(255) NOT NULL DEFAULT '' COMMENT '标签组应用范围',
                `tagNum` int(10) NOT NULL DEFAULT '0' COMMENT '标签组里的标签数量',
                `updatedTime` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
                `createdTime` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='标签组表';
        ");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('DROP TABLE IF EXISTS `tag_group`');
    }
}
