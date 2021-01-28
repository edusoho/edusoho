<?php

use Phpmig\Migration\Migration;

class BizOrderAddDeductTypeName extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        if (!$this->isFieldExist('biz_order_item_deduct', 'deduct_type_name')) {
            $connection->exec("ALTER TABLE `biz_order_item_deduct` ADD COLUMN `deduct_type_name` varchar(255)  NOT NULL DEFAULT '' COMMENT '优惠类型';");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("ALTER TABLE `biz_order_item_deduct` DROP COLUMN `deduct_type_name`;");
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