<?php

use Phpmig\Migration\Migration;

class UserAddIsStudent extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        if (!$this->isFieldExist('user', 'isStudent')) {
            $biz = $this->getContainer();
            $biz['db']->exec("ALTER TABLE `user` ADD COLUMN `isStudent` tinyint(1)  NOT NULL DEFAULT 1 COMMENT '是否为学员'");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        if ($this->isFieldExist('user', 'isStudent')) {
            $biz = $this->getContainer();
            $biz['db']->exec('ALTER TABLE `user` DROP COLUMN `isStudent`');
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
