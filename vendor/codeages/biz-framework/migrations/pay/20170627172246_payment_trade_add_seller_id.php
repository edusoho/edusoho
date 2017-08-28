<?php

use Phpmig\Migration\Migration;

class PaymentTradeAddSellerId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        if (!$this->isFieldExist('biz_payment_trade', 'seller_id')) {
            $db->exec(
                "ALTER TABLE `biz_payment_trade` Add column `seller_id` int(10) unsigned not null  COMMENT '卖家Id' after `platform_created_result`;"
            );
        }
        if (!$this->isFieldExist('biz_payment_trade', 'user_id')) {
            $db->exec(
                "ALTER TABLE `biz_payment_trade` Add column `user_id` int(10) unsigned not null  COMMENT '卖家Id' after `seller_id`;"
            );
        }
    }

    protected function isFieldExist($table, $filedName)
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $db->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
