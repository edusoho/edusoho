<?php

use Phpmig\Migration\Migration;

class ClassroomAddDisplay extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `classroom` ADD COLUMN `display` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开放逻辑展示' AFTER `showable`;
            update classroom set display = 0 where status = 'closed';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            ALTER TABLE `classroom` DROP COLUMN `display`;
        ');
    }
}
