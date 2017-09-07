<?php

use Phpmig\Migration\Migration;

class AddNumTypeToOrderItem extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        if (!$this->isFieldExist('biz_order_item', 'num')) {
            $connection->exec(
                "ALTER TABLE `biz_order_item` Add column `num` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '数量';"
            );
        }

        if (!$this->isFieldExist('biz_order_item', 'unit')) {
            $connection->exec(
                "ALTER TABLE `biz_order_item` Add column `unit` varchar(16) COMMENT '单位';"
            );
        }

        if (!$this->isFieldExist('biz_order_item', 'create_extra')) {
            $connection->exec(
                "ALTER TABLE `biz_order_item` Add column `create_extra` text COMMENT '创建时的自定义字段，json方式存储';"
            );
        }

        if (!$this->isFieldExist('biz_order', 'create_extra')) {
            $connection->exec(
                "ALTER TABLE `biz_order` Add column `create_extra` text COMMENT '创建时的自定义字段，json方式存储';"
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
