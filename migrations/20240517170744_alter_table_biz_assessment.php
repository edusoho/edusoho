<?php

use Phpmig\Migration\Migration;

class AlterTableBizAssessment extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        if ($this->isFieldExist('biz_assessment', 'name')) {
            $biz['db']->exec("
            ALTER TABLE `biz_assessment` ADD COLUMN `type` varchar(255) NOT NULL DEFAULT 'regular' COMMENT 'regular(固定卷),random(随机卷),ai_personality(AI个性卷)' AFTER `name`;
            ALTER TABLE `biz_assessment` ADD COLUMN `parent_id` int(10) NOT NULL DEFAULT 0 COMMENT '随机卷父试卷的ID' AFTER `type`;
        ");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        if ($this->isFieldExist('biz_assessment', 'type') && $this->isFieldExist('biz_assessment', 'parent_id')) {
            $biz['db']->exec('ALTER TABLE `biz_assessment` DROP COLUMN `type`');
            $biz['db']->exec('ALTER TABLE `biz_assessment` DROP COLUMN `parent_assessment_id`');
        }
    }

    private function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}` where Key_name='{$indexName}';";
        $result = $this->getContainer()['db']->fetchAssoc($sql);

        return !empty($result);
    }

    protected function isFieldExist($table, $filedName)
    {
        $biz = $this->getContainer();
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $biz['db']->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
