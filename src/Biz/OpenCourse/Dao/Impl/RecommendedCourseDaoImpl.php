<?php

namespace Topxia\Service\OpenCourse\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\OpenCourse\Dao\RecommendedCourseDao;

class RecommendedCourseDaoImpl extends BaseDao implements RecommendedCourseDao
{
    protected $table = 'open_course_recommend';

    public function getRecommendedCourse($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        }

        );
    }

    public function getRecommendedCourseByCourseIdAndType($openCourseId, $recommendCourseId, $type)
    {
        $that = $this;

        return $this->fetchCached("openCourseId:{$openCourseId}:recommendCourseId:{$recommendCourseId}:type:{$type}", $openCourseId, $recommendCourseId, $type, function ($openCourseId, $recommendCourseId, $type) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE openCourseId = ? AND recommendCourseId = ? AND type = ? ORDER BY seq ASC;";
            return $that->getConnection()->fetchAssoc($sql, array($openCourseId, $recommendCourseId, $type)) ?: null;
        }

        );
    }

    public function findRecommendedCoursesByOpenCourseId($openCourseId)
    {
        $that = $this;

        return $this->fetchCached("openCourseId:{$openCourseId}", $openCourseId, function ($openCourseId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE openCourseId = ? ORDER BY seq ASC;";
            return $that->getConnection()->fetchAll($sql, array($openCourseId)) ?: array();
        }

        );
    }

    public function addRecommendedCourse($recommended)
    {
        $recommended['createdTime'] = time();

        $affected = $this->getConnection()->insert($this->table, $recommended);
        $this->clearCached();

        if ($affected <= 0) {
            throw $this->createDaoException('Insert RecommendedCourse error.');
        }

        return $this->getRecommendedCourse($this->getConnection()->lastInsertId());
    }

    public function deleteRecommendedCourse($id)
    {
        $result = $this->getConnection()->delete($this->table, array('id' => $id));
        $this->clearCached();
        return $result;
    }

    public function updateRecommendedCourse($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        $this->clearCached();
        return $this->getRecommendedCourse($id);
    }

    public function deleteRecommendByOpenCouseIdAndRecommendCourseId($openCourseId, $recommendCourseId)
    {
        $sql    = "DELETE FROM {$this->table} WHERE openCourseId = ? AND recommendCourseId= ?";
        $result = $this->getConnection()->executeUpdate($sql, array($openCourseId, $recommendCourseId));
        $this->clearCached();
        return $result;
    }

    public function searchRecommendCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function searchRecommends($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $orderBy = $this->checkOrderBy($orderBy, array('createdTime', 'seq'));

        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ?: array();
    }

    public function findRandomRecommendCourses($courseId, $num)
    {
        $conditions = array(
            'openCourseId' => $courseId
        );

        $count = $this->searchRecommendCount($conditions);
        $max   = $count - $num - 1;
        if ($max < 0) {
            $max = 0;
        }
        $randomSeed = (int) rand(0, $max);
        $that       = $this;
        return $this->fetchCached("openCourseId:{$courseId}:randomSeed:{$randomSeed}:num:$num", $courseId, $randomSeed, $num, function ($courseId, $randomSeed, $num) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE openCourseId = ? LIMIT {$randomSeed}, $num";
            return $that->getConnection()->fetchAll($sql, array($courseId)) ?: array();
        });
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'open_course_recommend')
            ->andWhere('id = :id')
            ->andWhere('id IN (:ids)')
            ->andWhere('userId = :userId')
            ->andWhere('openCourseId = :openCourseId')
            ->andWhere('recommendCourseId = :recommendCourseId')
            ->andWhere('type = :type')
            ->andWhere('createdTime >= :startTimeGreaterThan')
            ->andWhere('createdTime < :startTimeLessThan');
        return $builder;
    }
}
