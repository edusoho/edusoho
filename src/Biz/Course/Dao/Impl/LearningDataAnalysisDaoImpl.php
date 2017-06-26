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

    public function getStatisticDataByCourseIdsAndUserId($courseIds, $userId)
    {
        $marks = str_repeat('?,', count($courseIds) - 1).'?';
        $sql = "SELECT SUM(c.publishedTaskNum) as publishedTaskNum,SUM(cm.learnedRequiredNum) as learnedRequiredNum FROM {$this->course} c JOIN {$this->course_member} cm ON c.id = cm.courseId WHERE cm.courseId IN ({$marks}) AND cm.userId = ?";

        return $this->db()->fetchAssoc($sql, array_merge($courseIds, array($userId)));
    }
}
