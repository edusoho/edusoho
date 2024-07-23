<?php

use Phpmig\Migration\Migration;

class BizAnswerSceneAddExamMode extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        if (!$this->isFieldExist('biz_answer_scene', 'exam_mode')) {
            $biz = $this->getContainer();
            $biz['db']->exec("ALTER TABLE `biz_answer_scene` ADD COLUMN `exam_mode` tinyint(1)  NOT NULL DEFAULT 0 COMMENT '考试模式类型 0模拟考试 1练习考试'");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        if ($this->isFieldExist('biz_answer_scene', 'exam_mode')) {
            $biz = $this->getContainer();
            $biz['db']->exec('ALTER TABLE `biz_answer_scene` DROP COLUMN `exam_mode`');
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
