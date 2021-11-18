<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\ActivityDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ActivityDaoImpl extends AdvancedDaoImpl implements ActivityDao
{
    protected $table = 'activity';

    public function findByCourseId($courseId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE fromCourseId = ?";

        return $this->db()->fetchAll($sql, [$courseId]) ?: [];
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function getByCopyIdAndCourseSetId($copyId, $courseSetId)
    {
        return $this->getByFields(['copyId' => $copyId, 'fromCourseSetId' => $courseSetId]);
    }

    public function findActivitiesByCourseSetId($courseSetId)
    {
        return $this->findByFields(['fromCourseSetId' => $courseSetId]);
    }

    public function findSelfVideoActivityByCourseIds($courseIds)
    {
        if (empty($courseIds)) {
            return [];
        }
        $sql = "select  a.*,  c.mediaId as fileId  from activity a left join activity_video c on a.mediaId = c.id where a.mediaType='video' and c.mediaSource='self' and a.fromCourseId in (".implode(',', $courseIds).')';

        return $this->db()->fetchAll($sql, []);
    }

    public function findActivitiesByMediaIdsAndMediaType($mediaIds, $mediaType)
    {
        $marks = str_repeat('?,', count($mediaIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE mediaId IN({$marks}) AND mediaType = ?;";

        return $this->db()->fetchAll($sql, array_merge($mediaIds, [$mediaType]));
    }

    public function findActivitiesByCourseIdAndType($courseId, $mediaType)
    {
        return $this->findByFields(['fromCourseId' => $courseId, 'mediaType' => $mediaType]);
    }

    public function findActivitiesByCourseSetIdAndType($courseSetId, $mediaType)
    {
        return $this->findByFields(['fromCourseSetId' => $courseSetId, 'mediaType' => $mediaType]);
    }

    public function findActivitiesByCourseSetIdsAndType($courseSetIds, $mediaType)
    {
        $marks = str_repeat('?,', count($courseSetIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE fromCourseSetId IN({$marks}) AND mediaType = ?;";

        return $this->db()->fetchAll($sql, array_merge($courseSetIds, [$mediaType]));
    }

    public function findActivitiesByCourseSetIdsAndTypes($courseSetIds, $mediaTypes)
    {
        $marks = str_repeat('?,', count($courseSetIds) - 1).'?';
        $marks1 = str_repeat('?,', count($mediaTypes) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE fromCourseSetId IN({$marks}) AND mediaType IN ({$marks1});";

        return $this->db()->fetchAll($sql, array_merge($courseSetIds, $mediaTypes));
    }

    public function findActivitiesByCourseIdsAndType($courseIds, $mediaType)
    {
        $marks = str_repeat('?,', count($courseIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE fromCourseId IN({$marks}) AND mediaType = ?;";

        return $this->db()->fetchAll($sql, array_merge($courseIds, [$mediaType]));
    }

    public function findActivitiesByCourseIdsAndTypes($courseIds, $mediaTypes)
    {
        if (empty($courseIds) || empty($mediaTypes)) {
            return [];
        }

        $marks = str_repeat('?,', count($courseIds) - 1).'?';
        $marks1 = str_repeat('?,', count($mediaTypes) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE fromCourseId IN({$marks}) AND mediaType IN ({$marks1});";

        return $this->db()->fetchAll($sql, array_merge($courseIds, $mediaTypes));
    }

    public function findFinishedLivesWithinTwoHours()
    {
        $currentTime = time();
        $expiredTime = 3600 * 2;
        $sql = "SELECT * FROM {$this->table} WHERE mediaType = 'live' AND {$currentTime} > endTime AND ({$currentTime} - endTime) < {$expiredTime};";

        return $this->db()->fetchAll($sql, []);
    }

    public function declares()
    {
        $declares['orderbys'] = ['endTime', 'startTime', 'createdTime'];
        $declares['conditions'] = [
            'id IN (:ids)',
            'fromCourseId = :fromCourseId',
            'mediaType = :mediaType',
            'fromCourseId IN (:courseIds)',
            'title like :title',
            'fromCourseId NOT IN (:excludeCourseIds)',
            'mediaType IN (:mediaTypes)',
            'mediaId IN (:mediaIds)',
            'mediaId = :mediaId',
            'fromCourseSetId = :fromCourseSetId',
            'fromCourseSetId IN (:courseSetIds)',
            'startTime >= :startTime_GT',
            'startTime <= :startTime_LT',
            'endTime <= :endTime_LT',
            'copyId = :copyId',
        ];

        return $declares;
    }

    public function findOverlapTimeActivitiesByCourseId($courseId, $newStartTime, $newEndTime, $excludeId = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE fromCourseId = ? AND (( startTime >= ? AND startTime <= ? ) OR ( startTime <= ? AND endTime >= ? ) OR ( endTime >= ? AND endTime <= ? ))";

        if ($excludeId) {
            $excludeId = intval($excludeId);
            $sql .= " AND id <> {$excludeId}";
        }

        return $this->db()->fetchAll($sql, [$courseId, $newStartTime, $newEndTime, $newStartTime, $newEndTime, $newStartTime, $newEndTime]);
    }

    public function getByMediaIdAndMediaTypeAndCopyId($mediaId, $mediaType, $copyId)
    {
        return $this->getByFields(['mediaId' => $mediaId, 'mediaType' => $mediaType, 'copyId' => $copyId]);
    }

    public function getByMediaIdAndMediaType($mediaId, $mediaType)
    {
        return $this->getByFields(['mediaId' => $mediaId, 'mediaType' => $mediaType]);
    }
}
