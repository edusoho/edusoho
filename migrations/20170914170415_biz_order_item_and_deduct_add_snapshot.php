<?php

use Phpmig\Migration\Migration;

class BizOrderItemAndDeductAddSnapshot extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        if (!$this->isFieldExist('biz_order_item', 'snapshot')) {
            $db->exec("ALTER TABLE `biz_order_item` ADD COLUMN `snapshot` text COMMENT '商品快照' AFTER `create_extra`");
        }

        if (!$this->isFieldExist('biz_order_item_deduct', 'snapshot')) {
            $db->exec("ALTER TABLE `biz_order_item_deduct` ADD COLUMN `snapshot` text COMMENT '促销快照' AFTER `seller_id`");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec("ALTER TABLE `biz_order_item` DROP COLUMN `snapshot`;");

        $db->exec("ALTER TABLE `biz_order_item_deduct` DROP COLUMN `snapshot`;");

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
