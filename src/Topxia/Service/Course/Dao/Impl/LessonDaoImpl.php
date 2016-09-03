<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\LessonDao;

class LessonDaoImpl extends BaseDao implements LessonDao
{
    protected $table = 'course_lesson';

    public function getLesson($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        }

        );
    }

    public function getLessonByCourseIdAndNumber($courseId, $number)
    {
        $that = $this;

        return $this->fetchCached("courseId:{$courseId}:number:{$number}", $courseId, $number, function ($courseId, $number) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE courseId = ? AND number = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($courseId, $number)) ?: null;
        }

        );
    }

    public function findLessonsByIds(array $ids)
    {
        if (empty($ids)) {
            return array();
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql   = "SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function findLessonsByCopyIdAndLockedCourseIds($copyId, array $courseIds)
    {
        if (empty($courseIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($courseIds) - 1).'?';

        $parmaters = array_merge(array($copyId), $courseIds);

        $sql = "SELECT * FROM {$this->table} WHERE  copyId = ? AND courseId IN ({$marks})";

        return $this->getConnection()->fetchAll($sql, $parmaters);
    }

    public function findLessonsByTypeAndMediaId($type, $mediaId)
    {
        $that = $this;

        return $this->fetchCached("type:{$type}:mediaId:{$mediaId}", $type, $mediaId, function ($type, $mediaId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE type = ? AND mediaId = ?";
            return $that->getConnection()->fetchAll($sql, array($type, $mediaId));
        }

        );
    }

    public function findLessonsByCourseId($courseId)
    {
        $that = $this;

        return $this->fetchCached("courseId:{$courseId}", $courseId, function ($courseId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE courseId = ? ORDER BY seq ASC";
            return $that->getConnection()->fetchAll($sql, array($courseId));
        }

        );
    }

    public function findMinStartTimeByCourseId($courseId)
    {
        $that = $this;

        return $this->fetchCached("courseId:{$courseId}:min:startTime", $courseId, function ($courseId) use ($that) {
            $sql = "select min(`startTime`) as startTime from {$that->getTable()} where courseId =?;";
            return $that->getConnection()->fetchAll($sql, array($courseId));
        }

        );
    }

    public function findLessonIdsByCourseId($courseId)
    {
        $that = $this;

        return $this->fetchCached("courseId:{$courseId}:lessonIds", $courseId, function ($courseId) use ($that) {
            $sql = "SELECT id FROM {$that->getTable()} WHERE  courseId = ? ORDER BY number ASC";
            return $that->getConnection()->executeQuery($sql, array($courseId))->fetchAll(\PDO::FETCH_COLUMN);
        }

        );
    }

    public function getLessonCountByCourseId($courseId)
    {
        $that = $this;

        return $this->fetchCached("courseId:{$courseId}:lessonCount", $courseId, function ($courseId) use ($that) {
            $sql = "SELECT COUNT(*) FROM {$that->getTable()} WHERE courseId = ? ";
            return $that->getConnection()->fetchColumn($sql, array($courseId));
        }

        );
    }

    public function searchLessons($conditions, $orderBy, $start, $limit)
    {
        if ($this->hasEmptyInCondition($conditions, array('courseIds'))) {
            return array();
        }

        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ?: array();
    }

    public function searchLessonCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function getLessonMaxSeqByCourseId($courseId)
    {
        $that = $this;

        return $this->fetchCached("courseId:{$courseId}:max:seq", $courseId, function ($courseId) use ($that) {
            $sql = "SELECT MAX(seq) FROM {$that->getTable()} WHERE  courseId = ?";
            return $that->getConnection()->fetchColumn($sql, array($courseId));
        }

        );
    }

    public function findTimeSlotOccupiedLessonsByCourseId($courseId, $startTime, $endTime, $excludeLessonId = 0)
    {
        $addtionalCondition = ";";

        $params = array($courseId, $startTime, $startTime, $startTime, $endTime);

        if (!empty($excludeLessonId)) {
            $addtionalCondition = "and id != ? ;";
            $params[]           = $excludeLessonId;
        }

        $sql = "SELECT * FROM {$this->table} WHERE courseId = ? AND ((startTime  < ? AND endTime > ?) OR  (startTime between ? AND ?)) ".$addtionalCondition;

        return $this->getConnection()->fetchAll($sql, $params);
    }

    public function findTimeSlotOccupiedLessons($startTime, $endTime, $excludeLessonId = 0)
    {
        $addtionalCondition = ";";

        $params = array($startTime, $startTime, $startTime, $endTime);

        if (!empty($excludeLessonId)) {
            $addtionalCondition = "and id != ? ;";
            $params[]           = $excludeLessonId;
        }

        $sql = "SELECT * FROM {$this->table} WHERE ((startTime  < ? AND endTime > ?) OR  (startTime between ? AND ?)) ".$addtionalCondition;

        return $this->getConnection()->fetchAll($sql, $params);
    }

    public function findLessonsByChapterId($chapterId)
    {
        $that = $this;

        return $this->fetchCached("chapterId:{$chapterId}", $chapterId, function ($chapterId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE chapterId = ? ORDER BY seq ASC";
            return $that->getConnection()->fetchAll($sql, array($chapterId));
        }

        );
    }

    public function addLesson($lesson)
    {
        $lesson['createdTime'] = time();
        $lesson['updatedTime'] = $lesson['createdTime'];
        $affected              = $this->getConnection()->insert($this->table, $lesson);
        $this->clearCached();

        if ($affected <= 0) {
            throw $this->createDaoException('Insert course lesson error.');
        }

        return $this->getLesson($this->getConnection()->lastInsertId());
    }

    public function updateLesson($id, $fields)
    {
        $fields['updatedTime'] = time();
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        $this->clearCached();
        return $this->getLesson($id);
    }

    public function deleteLessonsByCourseId($courseId)
    {
        $sql    = "DELETE FROM {$this->table} WHERE courseId = ?";
        $result = $this->getConnection()->executeUpdate($sql, array($courseId));
        $this->clearCached();
        return $result;
    }

    public function deleteLesson($id)
    {
        $result = $this->getConnection()->delete($this->table, array('id' => $id));
        $this->clearCached();
        return $result;
    }

    public function sumLessonGiveCreditByCourseId($courseId)
    {
        $that = $this;

        return $this->fetchCached("courseId:{$courseId}:sum:giveCredit", $courseId, function ($courseId) use ($that) {
            $sql = "SELECT SUM(giveCredit) FROM {$that->getTable()} WHERE  courseId = ?";
            return $that->getConnection()->fetchColumn($sql, array($courseId)) ?: 0;
        }

        );
    }

    public function sumLessonGiveCreditByLessonIds(array $lessonIds)
    {
        if (empty($lessonIds)) {
            return 0;
        }

        $marks = str_repeat('?,', count($lessonIds) - 1).'?';
        $sql   = "SELECT SUM(giveCredit) FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchColumn($sql, $lessonIds) ?: 0;
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        if (isset($conditions['title'])) {
            $conditions['titleLike'] = "%{$conditions['title']}%";
            unset($conditions['title']);
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, $this->table)
            ->andWhere('courseId = :courseId')
            ->andWhere('updatedTime >= :updatedTime_GE')
            ->andWhere('status = :status')
            ->andWhere('type = :type')
            ->andWhere('free = :free')
            ->andWhere('userId = :userId')
            ->andWhere('mediaId = :mediaId')
            ->andWhere('startTime >= :startTimeGreaterThan')
            ->andWhere('endTime < :endTimeLessThan')
            ->andWhere('startTime <= :startTimeLessThan')
            ->andWhere('endTime > :endTimeGreaterThan')
            ->andWhere('title LIKE :titleLike')
            ->andWhere('createdTime >= :startTime')
            ->andWhere('createdTime <= :endTime')
            ->andWhere('copyId = :copyId')
            ->andWhere('courseId IN ( :courseIds )');

        if (isset($conditions['notLearnedIds']) && !empty($conditions['notLearnedIds'])) {
            $builder->andWhere('id NOT IN ( :notLearnedIds)');
        }

        return $builder;
    }

    public function analysisLessonNumByTime($startTime, $endTime)
    {
        $that = $this;

        return $this->fetchCached("startTime:{$startTime}:endTime:{$endTime}:lessonNum", $startTime, $endTime, function ($startTime, $endTime) use ($that) {
            $sql = "SELECT count(id)  as num FROM `{$that->getTable()}` WHERE  `createdTime`>=? AND `createdTime`<=?  ";
            return $that->getConnection()->fetchColumn($sql, array($startTime, $endTime));
        }

        );
    }

    public function analysisLessonDataByTime($startTime, $endTime)
    {
        $that = $this;

        return $this->fetchCached("startTime:{$startTime}:endTime:{$endTime}:lesssonData", $startTime, $endTime, function ($startTime, $endTime) use ($that) {
            $sql = "SELECT count( id) as count, from_unixtime(createdTime,'%Y-%m-%d') as date FROM `{$that->getTable()}` WHERE  `createdTime`>=? AND `createdTime`<=? group by from_unixtime(`createdTime`,'%Y-%m-%d') order by date ASC ";
            return $that->getConnection()->fetchAll($sql, array($startTime, $endTime));
        }

        );
    }

    public function findFutureLiveDates($courseIds, $limit)
    {
        if (empty($courseIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($courseIds) - 1).'?';

        $time = time();

        $sql = "SELECT count( id) as count, from_unixtime(startTime,'%Y-%m-%d') as date FROM `{$this->getTable()}` WHERE  `type`= 'live' AND status='published' AND courseId IN ({$marks}) AND startTime >= {$time} group by date order by date ASC limit 0, {$limit}";
        return $this->getConnection()->fetchAll($sql, $courseIds);
    }

    public function findFutureLiveCourseIds()
    {
        $time = time();
        $sql  = "SELECT min(startTime) as startTime, courseId FROM {$this->table} WHERE endTime >= {$time} AND status='published' AND
                type = 'live' AND copyId = 0 GROUP BY courseId ORDER BY startTime ASC";

        return $this->getConnection()->fetchAll($sql);
    }

    public function findPastLiveCourseIds()
    {
        $time = time();
        $sql  = "SELECT max(startTime) as startTime, courseId FROM {$this->table} WHERE endTime < {$time} AND status='published' AND
                type = 'live' GROUP BY courseId ORDER BY startTime DESC";

        return $this->getConnection()->fetchAll($sql);
    }
}
