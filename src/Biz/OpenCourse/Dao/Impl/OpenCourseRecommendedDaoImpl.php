<?php

namespace Biz\OpenCourse\Dao\Impl;

use Biz\OpenCourse\Dao\OpenCourseRecommendedDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class OpenCourseRecommendedDaoImpl extends AdvancedDaoImpl implements OpenCourseRecommendedDao
{
    protected $table = 'open_course_recommend';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime'],
            'orderbys' => ['createdTime', 'seq'],
            'conditions' => [
                'id = :id',
                'id IN (:ids)',
                'userId = :userId',
                'openCourseId = :openCourseId',
                'recommendCourseId = :recommendCourseId',
                'recommendGoodsId = :recommendGoodsId',
                'type = :type',
                'createdTime >= :startTimeGreaterThan',
                'createdTime < :startTimeLessThan',
            ],
        ];
    }

    /**
     * @param $openCourseId
     * @param $goodsId
     *
     * @return array|null
     */
    public function getByOpenCourseIdAndGoodsId($openCourseId, $goodsId)
    {
        return $this->getByFields(['openCourseId' => $openCourseId, 'recommendGoodsId' => $goodsId]);
    }

    public function getByCourseIdAndType($openCourseId, $recommendCourseId, $type)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE openCourseId = ? AND recommendCourseId = ? AND type = ? ORDER BY seq ASC;";

        return $this->db()->fetchAssoc($sql, [$openCourseId, $recommendCourseId, $type]) ?: null;
    }

    public function findByOpenCourseId($openCourseId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE openCourseId = ? ORDER BY seq ASC;";

        return $this->db()->fetchAll($sql, [$openCourseId]) ?: [];
    }

    public function deleteByOpenCourseIdAndRecommendCourseId($openCourseId, $recommendCourseId)
    {
        $sql = "DELETE FROM {$this->table()} WHERE openCourseId = ? AND recommendCourseId= ?";

        return $this->db()->executeUpdate($sql, [$openCourseId, $recommendCourseId]);
    }

    public function findRandomRecommendCourses($courseId, $num)
    {
        $conditions = [
            'openCourseId' => $courseId,
        ];

        $count = $this->count($conditions);
        $num = (int) $num;
        $max = $count - $num - 1;
        if ($max < 0) {
            $max = 0;
        }
        $randomSeed = (int) mt_rand(0, $max);

        $sql = "SELECT * FROM {$this->table()} WHERE openCourseId = ? LIMIT {$randomSeed}, $num";

        return $this->db()->fetchAll($sql, [$courseId]) ?: [];
    }
}
