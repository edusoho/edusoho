<?php

use Phpmig\Migration\Migration;

class BizPaymentTradeAddCreatedParams extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        if (!$this->isFieldExist('biz_payment_trade', 'platform_created_params')) {
            $connection->exec("ALTER TABLE `biz_payment_trade` ADD COLUMN `platform_created_params` text COMMENT '在第三方系统创建支付订单时的参数信息'");
        }

    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('ALTER TABLE `biz_payment_trade` DROP COLUMN `platform_created_params`;');
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
