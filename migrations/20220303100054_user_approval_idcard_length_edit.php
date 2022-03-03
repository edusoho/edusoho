<?php

use Phpmig\Migration\Migration;

class UserApprovalIdcardLengthEdit extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        if (!$this->isFieldExist('user_approval', 'idcard')) {
            $biz = $this->getContainer();
            $biz['db']->exec('ALTER TABLE `user_approval` MODIFY `idcard` VARCHAR(51);');
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }

    protected function isFieldExist($table, $filedName)
    {
        $biz = $this->getContainer();
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $biz['db']->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
