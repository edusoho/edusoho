<?php

use Phpmig\Migration\Migration;

class OrderAddPriceType extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        if (!$this->isFieldExist('orders', 'price_type')) {
            $db->exec(
                "ALTER TABLE `orders` Add column `price_type` varchar(32) not null  COMMENT '标价类型，现金支付or虚拟币；money, coin' after `price_amount`;"
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
