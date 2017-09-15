<?php

use Phpmig\Migration\Migration;

class BizUserCashflowDeleteUserType extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        if ($this->isFieldExist('biz_user_cashflow', 'user_type')) {
            $connection->exec("ALTER TABLE `biz_user_cashflow` DROP COLUMN `user_type`");
        }

    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
    }

    protected function isFieldExist($table, $filedName)
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $db->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
