<?php

use Phpmig\Migration\Migration;

class AddOrderSignStatus extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        if (!$this->isFieldExist('biz_order', 'signed_time')) {
            $db->exec(
                "ALTER TABLE `biz_order` Add column `signed_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '签收时间';"
            );

            $db->exec(
                "ALTER TABLE `biz_order` Add column `signed_data` TEXT COMMENT '签收的信息';"
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
