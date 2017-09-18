<?php

use Phpmig\Migration\Migration;

class BizOrderAddDevice extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        if (!$this->isFieldExist('biz_order', 'device')) {
            $db->exec("ALTER TABLE `biz_order` ADD COLUMN `device` varchar(32) COMMENT '下单设备';");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec("ALTER TABLE `biz_order` DROP COLUMN `device`;");
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
