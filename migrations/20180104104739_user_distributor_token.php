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

        if (!$this->isFieldExist('user', 'distributorToken')) {
            $db->exec("ALTER TABLE `user` ADD `distributorToken` varchar(255) NOT NULL DEFAULT '' COMMENT '分销平台token';");
        }
    }

    protected function isFieldExist($table, $fieldName)
    {
        $container = $this->getContainer();

        $sql = "DESCRIBE `{$table}` `$fieldName`";
        $result = $container['db']->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
