<?php

use Phpmig\Migration\Migration;

class BizOrderRefundItemAddTarget extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        if (!$this->isFieldExist('biz_order_item_refund', 'target_id')) {
            $db->exec("ALTER TABLE `biz_order_item_refund` ADD COLUMN `target_id` INT(10) unsigned NOT NULL COMMENT '商品id'");
        }

        if (!$this->isFieldExist('biz_order_item_refund', 'target_type')) {
            $db->exec("ALTER TABLE `biz_order_item_refund` ADD COLUMN `target_type` VARCHAR(32) NOT NULL COMMENT '商品类型'");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec("ALTER TABLE `biz_order_item_refund` DROP COLUMN `target_id`;");
        $db->exec("ALTER TABLE `biz_order_item_refund` DROP COLUMN `target_type`;");
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
