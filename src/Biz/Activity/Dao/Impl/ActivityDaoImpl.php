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

    public function findFinishedLivesWithinTwoHours()
    {
        $currentTime = time();
        $expiredTime = 3600 * 2;
        $sql = "SELECT * FROM {$this->table} WHERE mediaType = 'live' AND {$currentTime} > endTime AND ({$currentTime} - endTime) < {$expiredTime};";

        return $this->db()->fetchAll($sql, []);
    }

    public function declares()
    {
        $declares['orderbys'] = ['endTime'];
        $declares['conditions'] = [
            'fromCourseId = :fromCourseId',
            'mediaType = :mediaType',
            'fromCourseId IN (:courseIds)',
            'mediaType IN (:mediaTypes)',
            'mediaId = :mediaId',
            'fromCourseSetId = :fromCourseSetId',
            'startTime >= :startTime_GT',
            'endTime <= :endTime_LT',
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
