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

    public function update($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        $this->clearCached();
        return $this->getRecommendedCourse($id);
    }

    public function findRecommendCourse($openCourseId, $recommendCourseId)
    {
        $that = $this;

        return $this->fetchCached("openCourseId:{$openCourseId}:recommendCourseId:{$recommendCourseId}", $openCourseId, $recommendCourseId, function ($openCourseId, $recommendCourseId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} where openCourseId = ? AND recommendCourseId = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($openCourseId, $recommendCourseId)) ?: null;
        }

        );
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
        $orderBy = $this->checkOrderBy($orderBy, array('createdTime'));

        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ?: array();
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'open_course_recommend')
            ->andWhere('userId = :userId')
            ->andWhere('openCourseId = :openCourseId')
            ->andWhere('origin = :origin')
            ->andWhere('recommendCourseId = :recommendCourseId')
            ->andWhere('type = :type')
            ->andWhere('createdTime >= :startTimeGreaterThan')
            ->andWhere('createdTime < :startTimeLessThan')
        ;
        return $builder;
    }
}
