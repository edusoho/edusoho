<?php
namespace Codeages\Biz\ItemBank\Answer\Dao\Impl;

use Codeages\Biz\ItemBank\Answer\Dao\AnswerSceneDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class AnswerSceneDaoImpl extends AdvancedDaoImpl implements AnswerSceneDao
{
    protected $table = 'biz_answer_scene';

    public function declares()
    {
        return [
            'timestamps' => [
                'created_time',
                'updated_time'
            ],
            'orderbys' => [
                'created_time'
            ],
            'serializes' => [],
            'conditions' => [
                'id IN (:ids)',
            ],
        ];
    }

    /**
     * @param int $limited
     * @return array
     * 获取有新的提交行为，但是没有数据统计的部分
     */
    public function findNotStatisticsQuestionsReportScenes($limited = 100)
    {
        $sql = "SELECT * FROM {$this->table} WHERE question_report_update_time < last_review_time ORDER BY `question_report_update_time` ASC limit ?;";
        return $this->db()->fetchAll($sql, [$limited]);
    }
}
