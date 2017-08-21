<?php

use Phpmig\Migration\Migration;

class RenameOrder extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        if ($this->isTableExist('orders')) {
            $db->exec(
                "RENAME TABLE orders TO biz_order"
                
            );
        }

        if ($this->isTableExist('order_item_deduct')) {
            $db->exec(
                "RENAME TABLE order_item_deduct TO biz_order_item_deduct;"
            );
        }

        if ($this->isTableExist('order_refund')) {
            $db->exec(
                "RENAME TABLE order_refund TO biz_order_refund;"
            );
        }

        if ($this->isTableExist('order_item')) {
            $db->exec(
                "RENAME TABLE order_item TO biz_order_item;"
            );
        }
    }

    protected function isTableExist($table)
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $sql = "SHOW TABLES LIKE '{$table}'";
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
