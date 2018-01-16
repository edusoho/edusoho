<?php

use Phpmig\Migration\Migration;

class AddBizOrderItemDeductField extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];

        if (!$this->isFieldExist('biz_order_item_deduct', 'deduct_type_name')) {
            $db->exec("ALTER TABLE `biz_order_item_deduct` ADD COLUMN `deduct_type_name` varchar(255)  NOT NULL DEFAULT '' COMMENT '优惠类型' AFTER `deduct_type`");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db'];

        if ($this->isFieldExist('biz_order_item_deduct', 'deduct_type_name')) {
            $db->exec('ALTER TABLE `biz_order_item_deduct` DROP `deduct_type_name`;');
        }
    }

    protected function isFieldExist($table, $fieldName)
    {
        $container = $this->getContainer();

        $sql = "DESCRIBE `{$table}` `$fieldName`";
        $result = $container['db']->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
