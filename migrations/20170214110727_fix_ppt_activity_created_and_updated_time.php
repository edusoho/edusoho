<?php

use Phpmig\Migration\Migration;

class FixPptActivityCreatedAndUpdatedTime extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `ppt_activity` CHANGE `createdTime` `createdTime` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '';");
        $biz['db']->exec("ALTER TABLE `ppt_activity` CHANGE `updatedTime` `updatedTime` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '';");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
    }
}
