<?php

use Phpmig\Migration\Migration;

class RenameTargetLog extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("RENAME TABLE `target_log` TO `biz_targetlog`;");
        $connection->exec("ALTER TABLE `biz_targetlog` ADD INDEX `idx_target` (`target_type`(8), `target_id`(8));");
        $connection->exec("ALTER TABLE `biz_targetlog` ADD INDEX `idx_level` (`level`);");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
