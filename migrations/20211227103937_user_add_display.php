<?php

use Phpmig\Migration\Migration;

class UserAddDisplay extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        if (!$this->isFieldExist('user', 'display')) {
            $biz = $this->getContainer();
            $biz['db']->exec("ALTER TABLE `user` ADD COLUMN `display` tinyint(1) unsigned  NOT NULL DEFAULT 1 COMMENT '在网校显示'");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        if ($this->isFieldExist('user', 'display')) {
            $biz = $this->getContainer();
            $biz['db']->exec('ALTER TABLE `user` DROP COLUMN `display`');
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
