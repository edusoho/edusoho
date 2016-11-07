<?php

use Phpmig\Migration\Migration;

class UserRegisterWay extends Migration
{
    private function getDb()
    {
        $biz = $this->getContainer();
        return $biz['db'];
    }

    /**
     * Do the migration
     */
    public function up()
    {
        $db = $this->getDb();
        if (!$this->isFieldExist('user', 'registeredWay')) {
            $db->exec("ALTER TABLE `user` ADD `registeredWay` varchar(64) NOT NULL DEFAULT '' COMMENT '注册设备来源(web/ios/android)'");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }

    private function isFieldExist($table, $field)
    {
        $db     = $this->getDb();
        $sql    = "DESCRIBE `{$table}` `{$field}`;";
        $result = $db->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

}
