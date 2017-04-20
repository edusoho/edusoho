<?php

use Phpmig\Migration\Migration;

class OldCloudAppAddProtocol extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("ALTER TABLE `cloud_app` ADD `protocol` TINYINT UNSIGNED NOT NULL DEFAULT '2' AFTER `type`;");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('ALTER TABLE `cloud_app` DROP `protocol`;');
    }
}
