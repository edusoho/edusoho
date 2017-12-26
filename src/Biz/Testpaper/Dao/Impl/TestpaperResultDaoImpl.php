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

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }


    public function sumScoreByParams($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('sum(score)');

        return $builder->execute()->fetchColumn(0);
    }

    public function declares()
    {
        return array(
            'orderbys' => array(
                'id',
                'testId',
                'courseId',
                'lessonId',
                'beginTime',
                'endTime',
                'checkedTime',
                'updateTime',
            ),
            'conditions' => array(
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
            ),
            'serializes' => array(
                'metas' => 'json',
            ),
        );
    }
}
