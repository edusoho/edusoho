<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\LearningDataAnalysisDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class LearningDataAnalysisDaoImpl extends GeneralDaoImpl implements LearningDataAnalysisDao
{
    protected $course_member = 'course_member';
    protected $course = 'course_v8';

    public function declares()
    {
        return array(
        );
    }

    public function sumStatisticDataByCourseIdsAndUserId($courseIds, $userId)
    {
        if (empty($courseIds)) {
            return array('compulsoryTaskNum' => 0, 'learnedCompulsoryTaskNum' => 0);
        }

        $marks = str_repeat('?,', count($courseIds) - 1).'?';
        $sql = "SELECT SUM(c.compulsoryTaskNum) as compulsoryTaskNum,SUM(cm.learnedCompulsoryTaskNum) as learnedCompulsoryTaskNum FROM {$this->course} c JOIN {$this->course_member} cm ON c.id = cm.courseId WHERE cm.courseId IN ({$marks}) AND cm.userId = ?";

        return $this->db()->fetchAssoc($sql, array_merge($courseIds, array($userId)));
    }

    /**
     * 批量更新计数
     *
     * @param $courseId
     * @param $userIds
     */
    public function batchRefreshUserLearningData($courseId, $userIds)
    {
        $userIds = implode(',', array_map('intval', $userIds));
        $courseId = intval($courseId);

        $sql = "UPDATE `course_member` AS cm SET learnedNum = (SELECT COUNT(ctr1.id) FROM course_task AS ct1 JOIN `course_task_result` AS ctr1 ON ct1.id = ctr1.courseTaskId WHERE ctr1.userId = cm.userId AND ctr1.courseId = cm.courseId AND ctr1.status = 'finish'), learnedCompulsoryTaskNum = (SELECT COUNT(ctr2.id) FROM course_task AS ct JOIN course_task_result AS ctr2 ON ct.id = ctr2.courseTaskId WHERE ctr2.userId = cm.userId AND ct.courseId = cm.courseId AND ctr2.status = 'finish' AND ct.isOptional = 0) WHERE cm.courseId = {$courseId} AND cm.userId IN ({$userIds})";

        return $this->db()->executeUpdate($sql, array($courseId));
    }
}
