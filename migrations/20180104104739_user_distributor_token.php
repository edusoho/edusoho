<?php

use Phpmig\Migration\Migration;

class UserDistributorToken extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];

        $db->exec("ALTER TABLE `user` ADD `distributorToken` varchar(255) NOT NULL DEFAULT '' COMMENT '分销平台token';");
        $db->exec('ALTER TABLE `user` ADD INDEX `distributorToken` (`distributorToken`);');
    }
}
