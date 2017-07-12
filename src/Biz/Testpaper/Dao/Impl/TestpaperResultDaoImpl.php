<?php

namespace Biz\Testpaper\Dao\Impl;

use Biz\Testpaper\Dao\TestpaperResultDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TestpaperResultDaoImpl extends GeneralDaoImpl implements TestpaperResultDao
{
    protected $table = 'testpaper_result_v8';

    public function getUserUnfinishResult($testId, $courseId, $activityId, $type, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE testId = ? AND courseId = ? AND lessonId = ? AND type = ? AND userId = ? AND status != 'finished' ORDER BY id DESC ";

        return $this->db()->fetchAssoc($sql, array($testId, $courseId, $activityId, $type, $userId)) ?: null;
    }

    public function getUserFinishedResult($testId, $courseId, $activityId, $type, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE testId = ? AND courseId = ? AND lessonId = ? AND type = ? AND userId = ? AND status = 'finished' ORDER BY id DESC ";

        return $this->db()->fetchAssoc($sql, array($testId, $courseId, $activityId, $type, $userId)) ?: null;
    }

    public function findLatelyTestpaperFinishedResultsByTaskIdsAndUserIdsAndStatus($userIds,$taskIds,$status)
    {
        if(empty($userIds) or empty($taskIds)) {
            return array();
        }
        $userIdMarks = str_repeat('?,', count($userIds) - 1).'?';
        $lessonIdMarks = str_repeat('?,', count($taskIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} a,(SELECT id,max(beginTime) AS beginTime FROM {$this->table} WHERE lessonId IN ({$lessonIdMarks}) AND userId IN ({$userIdMarks}) AND status = ? AND type= 'testpaper' GROUP BY userId,lessonId) b WHERE a.id=b.id AND a.beginTime =b.beginTime;";

        return $this->db()->fetchAll($sql,array_merge($taskIds,$userIds,array($status))) ?: array();
    }

    public function getUserLatelyResultByTestId($userId, $testId, $courseId, $activityId, $type)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND testId = ? AND courseId = ? AND lessonId = ? AND type = ? ORDER BY id DESC ";

        return $this->db()->fetchAssoc($sql, array($userId, $testId, $courseId, $activityId, $type)) ?: null;
    }

    public function findPaperResultsStatusNumGroupByStatus($testId, $courseIds)
    {
        if (empty($courseIds)) {
            return array();
        }
        $marks = str_repeat('?,', count($courseIds) - 1).'?';

        $sql = "SELECT status,COUNT(id) AS num FROM {$this->table} WHERE testId=? AND courseId IN ($marks)  GROUP BY status";

        return $this->db()->fetchAll($sql, array_merge(array($testId), $courseIds)) ?: array();
    }

    public function sumScoreByParames($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('sum(score)');

        return $builder->execute()->fetchColumn(0);
    }

    public function declares()
    {
        $declares['orderbys'] = array(
            'id',
            'testId',
            'courseId',
            'lessonId',
            'beginTime',
            'endTime',
            'checkedTime',
            'updateTime',
        );

        $declares['conditions'] = array(
            'id = :id',
            'checkTeacherId = :checkTeacherId',
            'paperName = :paperName',
            'testId = :testId',
            'testId IN ( :testIds )',
            'courseId = :courseId',
            'userId = :userId',
            'userId IN (:userIds)',
            'score = :score',
            'objectiveScore = :objectiveScore',
            'subjectiveScore = :subjectiveScore',
            'rightItemCount = :rightItemCount',
            'status = :status',
            'courseId IN ( :courseIds)',
            'type = :type',
            'type IN ( :types )',
            'lessonId = :lessonId',
        );

        return $declares;
    }
}
