<?php

use Phpmig\Migration\Migration;

class AddBizItemAttachmentColumn extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        if (!$this->isFieldExist('biz_item_attachment', 'seq')) {
            $biz = $this->getContainer();
            $biz['db']->exec("ALTER TABLE `biz_item_attachment` ADD COLUMN `seq` int(10)  NOT NULL DEFAULT 1 COMMENT '排序'");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        if ($this->isFieldExist('biz_item_attachment', 'seq')) {
            $biz = $this->getContainer();
            $biz['db']->exec('ALTER TABLE `biz_item_attachment` DROP COLUMN `seq`');
        }
    }

    protected function isFieldExist($table, $filedName)
    {
        $biz = $this->getContainer();
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $biz['db']->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
