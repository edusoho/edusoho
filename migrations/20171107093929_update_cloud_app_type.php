<?php

use Phpmig\Migration\Migration;

class UpdateCloudAppType extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("
            ALTER TABLE `cloud_app` MODIFY `type` varchar(64) NOT NULL DEFAULT 'plugin' COMMENT '应用类型(core系统，plugin插件应用, theme主题应用)'");
        $connection->exec("
            UPDATE `cloud_app` SET type ='core' WHERE code = 'MAIN'");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("
            ALTER TABLE `cloud_app` MODIFY `type` enum('plugin','theme') NOT NULL DEFAULT 'plugin' COMMENT '应用类型(plugin插件应用, theme主题应用)'");
        $connection->exec("
            UPDATE `cloud_app` SET type ='plugin' WHERE code = 'MAIN'");
    }
}
