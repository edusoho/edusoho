<?php

use Phpmig\Migration\Migration;

class AddBizAnswerReportIndex extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $connection = $this->getContainer()['db'];

        if (!$this->isIndexExist('biz_answer_record', 'assessment_id', 'assessment_id')) {
            $connection->exec('ALTER TABLE `biz_answer_record` ADD INDEX assessment_id ( `assessment_id`);');
        }

        if (!$this->isIndexExist('biz_answer_report', 'assessment_id', 'assessment_id')) {
            $connection->exec('ALTER TABLE `biz_answer_report` ADD INDEX assessment_id ( `assessment_id`);');
        }

        if (!$this->isIndexExist('biz_answer_question_report', 'assessment_id', 'assessment_id')) {
            $connection->exec('ALTER TABLE `biz_answer_question_report` ADD INDEX assessment_id ( `assessment_id`);');
        }

    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        if ($this->isIndexExist('biz_answer_record', 'assessment_id', 'assessment_id')) {
            $biz['db']->exec('ALTER TABLE `biz_answer_record` DROP INDEX `assessment_id`;');
        }

        if ($this->isIndexExist('biz_answer_report', 'assessment_id', 'assessment_id')) {
            $biz['db']->exec('ALTER TABLE `biz_answer_report` DROP INDEX `assessment_id`;');
        }

        if ($this->isIndexExist('biz_answer_question_report', 'assessment_id', 'assessment_id')) {
            $biz['db']->exec('ALTER TABLE `biz_answer_question_report` DROP INDEX `assessment_id`;');
        }
    }

    protected function isIndexExist($table, $filedName, $indexName)
    {
        $sql = "show index from `{$table}` where column_name = '{$filedName}' and Key_name = '{$indexName}';";
        $result = $this->getContainer()['db']->fetchAssoc($sql);

        return !empty($result);
    }
}
