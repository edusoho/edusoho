<?php

namespace Biz\OpenCourse\Dao\Impl;

use Biz\OpenCourse\Dao\RecommendedCourseDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class RecommendedCourseDaoImpl extends AdvancedDaoImpl implements RecommendedCourseDao
{
    protected $table = 'open_course_recommend';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'orderbys' => array('createdTime', 'recommendedSeq', 'studentNum', 'hitNum', 'seq'),
            'conditions' => array(
                'id = :id',
                'id IN (:ids)',
                'userId = :userId',
                'openCourseId = :openCourseId',
                'recommendCourseId = :recommendCourseId',
                'type = :type',
                'createdTime >= :startTimeGreaterThan',
                'createdTime < :startTimeLessThan',
            ),
        );
    }

    public function getByCourseIdAndType($openCourseId, $recommendCourseId, $type)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE openCourseId = ? AND recommendCourseId = ? AND type = ? ORDER BY seq ASC;";

        return $this->db()->fetchAssoc($sql, array($openCourseId, $recommendCourseId, $type)) ?: null;
    }

    public function findByOpenCourseId($openCourseId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE openCourseId = ? ORDER BY seq ASC;";

        return $this->db()->fetchAll($sql, array($openCourseId)) ?: array();
    }

    public function deleteByOpenCourseIdAndRecommendCourseId($openCourseId, $recommendCourseId)
    {
        $sql = "DELETE FROM {$this->table()} WHERE openCourseId = ? AND recommendCourseId= ?";

        return $this->db()->executeUpdate($sql, array($openCourseId, $recommendCourseId));
    }

    public function findRandomRecommendCourses($courseId, $num)
    {
        $conditions = array(
            'openCourseId' => $courseId,
        );

        $count = $this->count($conditions);
        $num = (int) $num;
        $max = $count - $num - 1;
        if ($max < 0) {
            $max = 0;
        }
        $randomSeed = (int) mt_rand(0, $max);

        $sql = "SELECT * FROM {$this->table()} WHERE openCourseId = ? LIMIT {$randomSeed}, $num";

        return $this->db()->fetchAll($sql, array($courseId)) ?: array();
    }
}
