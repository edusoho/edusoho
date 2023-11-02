<?php

use Phpmig\Migration\Migration;

class AddBizAnswerRecordColumn extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        if (!$this->isFieldExist('biz_answer_record', 'isTag')) {
            $biz = $this->getContainer();
            $biz['db']->exec("ALTER TABLE `biz_answer_record` ADD COLUMN `isTag` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否标记题目';");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $connection = $this->getContainer()['db'];
        if ($this->isFieldExist('biz_answer_record', 'isTag')) {
            $connection->exec('ALTER TABLE `biz_answer_record` DROP COLUMN `isTag`');
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
