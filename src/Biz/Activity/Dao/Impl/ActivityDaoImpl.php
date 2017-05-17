<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\ActivityDao;
use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ActivityDaoImpl extends GeneralDaoImpl implements ActivityDao
{
    protected $table = 'activity';

    public function findByCourseId($courseId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE fromCourseId = ?";

        return $this->db()->fetchAll($sql, array($courseId)) ?: array();
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function getByCopyIdAndCourseSetId($copyId, $courseSetId)
    {
        return $this->getByFields(array('copyId' => $copyId, 'fromCourseSetId' => $courseSetId));
    }

    public function isCourseVideoTryLookable($courseIds)
    {
        if (empty($courseIds)) {
            return array();
        }

        $sql = "select a.fromCourseId as courseId, count(c.id) as count from activity a left join activity_video c on a.mediaId = c.id where a.mediaType='video' and c.mediaSource='cloud' and a.fromCourseId in (".implode(',', $courseIds).') group by a.fromCourseId having count > 0';
        $result = $this->db()->fetchAll($sql, array());
        $map = array();
        if (!empty($result)) {
            $result = ArrayToolkit::index($result, 'courseId');
            foreach ($courseIds as $id) {
                $map[$id] = empty($result[$id]) ? 0 : 1;
            }
        }

        return $map;
    }

    public function declares()
    {
        $declares['conditions'] = array(
            'fromCourseId = :fromCourseId',
            'mediaType = :mediaType',
            'fromCourseId IN (:courseIds)',
            'mediaType IN (:mediaTypes)',
            'mediaId = :mediaId',
        );

        return $declares;
    }

    public function findOverlapTimeActivitiesByCourseId($courseId, $newStartTime, $newEndTime)
    {
        $sql = "SELECT * FROM {$this->table} WHERE fromCourseId = ? AND ((startTime < ? AND endTime > ?) OR (startTime between ? AND ?))";

        return $this->db()->fetchAll($sql, array($courseId, $newStartTime, $newEndTime, $newStartTime, $newEndTime));
    }
}
