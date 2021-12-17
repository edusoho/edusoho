<?php

use Phpmig\Migration\Migration;

class GroupsAddRecommend extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `groups` ADD COLUMN `recommended` tinyint(1) unsigned  NOT NULL DEFAULT '0' COMMENT '是否推荐' after `ownerId`");
        $biz['db']->exec("ALTER TABLE `groups` ADD COLUMN `recommendedSeq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐序号' after `recommended`");
        $biz['db']->exec("ALTER TABLE `groups` ADD COLUMN `recommendedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间' after `recommendedSeq`");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
          ALTER TABLE `groups` DROP COLUMN `recommended`;
          ALTER TABLE `groups` DROP COLUMN `recommendedSeq`;
          ALTER TABLE `groups` DROP COLUMN `recommendedTime`;
        ');
    }
}
