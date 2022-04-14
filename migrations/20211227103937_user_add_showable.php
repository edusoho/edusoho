<?php

use Phpmig\Migration\Migration;

class UserAddShowable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        if (!$this->isFieldExist('user', 'showable')) {
            $biz = $this->getContainer();
            $biz['db']->exec("ALTER TABLE `user` ADD COLUMN `showable` tinyint(1) unsigned  NOT NULL DEFAULT 1 COMMENT '在网校显示'");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        if ($this->isFieldExist('user', 'showable')) {
            $biz = $this->getContainer();
            $biz['db']->exec('ALTER TABLE `user` DROP COLUMN `showable`');
        }
    }

    protected function isFieldExist($table, $filedName)
    {
        $biz = $this->getContainer();
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $biz['db']->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
