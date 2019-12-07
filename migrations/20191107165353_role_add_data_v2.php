<?php

use Phpmig\Migration\Migration;

class RoleAddDataV2 extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];

        $db->exec("ALTER TABLE `role` ADD `data_v2` text COMMENT 'admin_v2权限配置' AFTER `data`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec('ALTER TABLE `role` DROP column `data_v2`;');
    }
}
