<?php

use Phpmig\Migration\Migration;

class CreateTableBizAssessmentGenerateRule extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        if (!$this->isTableExist('biz_assessment_generate_rule')) {
            $biz['db']->exec("
            CREATE TABLE `biz_assessment_generate_rule` (
            `id` int(10) NOT NULL,
            `num` int(10) NOT NULL COMMENT '试卷份数',
            `type` varchar(255) NOT NULL COMMENT '抽题方式(按题型抽题questionType，题型分类questionTypeCategory)',
            `assessment_id` int(10) NOT NULL COMMENT '试卷编号',
            `question_setting` text NOT NULL COMMENT '题目设置',
            `difficulty` varchar(255) NULL COMMENT '难度调节',
            `wrong_question_rate` int(10) NULL COMMENT '错题比例',
            `created_time` int(10) NULL,
            `updated_time` int(10) NULL,
            PRIMARY KEY (`id`)
            )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='试卷生成规则表';
        ");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        if ($this->isTableExist('biz_assessment_generate_rule')) {
            $biz['db']->exec('DROP TABLE IF EXISTS `biz_assessment_generate_rule`;');
        }
    }

    private function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}` where Key_name='{$indexName}';";
        $result = $this->getContainer()['db']->fetchAssoc($sql);

        return !empty($result);
    }

    protected function isTableExist($table)
    {
        $biz = $this->getContainer();
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $biz['db']->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
