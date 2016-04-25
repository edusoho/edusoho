<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\CourseMaterialDao;

class CourseMaterialDaoImpl extends BaseDao implements CourseMaterialDao
{
    protected $table = 'course_material';

    public function getMaterial($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function addMaterial($material)
    {
        $affected = $this->getConnection()->insert($this->table, $material);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert material error.');
        }

        return $this->getMaterial($this->getConnection()->lastInsertId());
    }

    public function deleteMaterial($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function searchMaterials($conditions, $orderBy, $start, $limit)
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

    public function searchMaterialCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        if (isset($conditions['title'])) {
            $conditions['titleLike'] = "%{$conditions['title']}%";
            unset($conditions['title']);
        }

        $builder = $this->createDynamicQueryBuilder($conditions)

            ->from($this->table, $this->table)
            ->andWhere('id = :id')
            ->andWhere('courseId = :courseId')
            ->andWhere('lessonId = :lessonId')
            ->andWhere('type = :type')
            ->andWhere('userId = :userId')
            ->andWhere('title LIKE :titleLike')
            ->andWhere('copyId = :copyId')
            ->andWhere('fileId = :fileId')
            ->andWhere('courseId IN (:courseIds)');

        return $builder;
    }
}
