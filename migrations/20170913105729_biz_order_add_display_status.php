<?php

use Phpmig\Migration\Migration;

class BizOrderAddDisplayStatus extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        if (!$this->isFieldExist('biz_order', 'display_status')) {
            $db->exec("ALTER TABLE `biz_order` ADD COLUMN `display_status` varchar(32) NOT NULL DEFAULT 'no_paid' COMMENT '订单显示状态(no_paid,paid,refunding,closed,refunded)' AFTER `status`");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec("ALTER TABLE `biz_order` DROP COLUMN `display_status`;");
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
