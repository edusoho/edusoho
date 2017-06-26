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
            'timestamps' => array('createdTime'),
            'orderbys' => array('replayId', 'createdTime', 'id'),
            'conditions' => array(
                'courseId = :courseId',
                'userId = :userId',
                'type = :type',
                'createdTime >= :createdTime_GE',
                'courseSetId = :courseSetId',
                'courseSetId IN ( :courseSetIds )',
                'courseId NOT IN ( :excludeCourseIds )',
            ),
        );
    }

    public function countStatisticDataByCourseIdsAndUserId($courseIds, $userId)
    {
        $marks = str_repeat('?,', count($courseIds) - 1).'?';
        $sql = "SELECT COUNT(c.publishedTaskNum) as publishedTaskNum,COUNT(cm.learnedRequiredNum) as learnedRequiredNum FROM {$this->course} c JOIN {$this->course_member} cm ON c.id = cm.courseId WHERE cm.courseId IN ({$marks}) AND cm.userId = ?";

        return $this->db()->fetchAssoc($sql, array_merge($courseIds, array($userId)));
    }
}
