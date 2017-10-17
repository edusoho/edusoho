<?php

use Phpmig\Migration\Migration;

class BizOrderAddSuccessFailData extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        if (!$this->isFieldExist('biz_order', 'success_data')) {
            $db->exec("ALTER TABLE `biz_order` ADD COLUMN `success_data` text COMMENT '当订单改变为success时的数据记录';");
        }

        if (!$this->isFieldExist('biz_order', 'fail_data')) {
            $db->exec("ALTER TABLE `biz_order` ADD COLUMN `fail_data` text COMMENT '当订单改变为fail时的数据记录'");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec("ALTER TABLE `biz_order` DROP COLUMN `paid_cash_amount`;");
        $db->exec("ALTER TABLE `biz_order` DROP COLUMN `paid_coin_amount`;");
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
