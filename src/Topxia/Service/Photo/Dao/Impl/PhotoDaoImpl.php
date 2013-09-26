<?php

namespace Topxia\Service\Photo\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Photo\Dao\PhotoDao;

class PhotoDaoImpl extends BaseDao implements PhotoDao
{
    protected $table = 'photo_group';

    public function getPhoto($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }
    
    public function findPhotoByIds(array $ids)
    {
        if(empty($ids)){
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function searchPhotos($conditions, $orderBy, $start, $limit)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array(); 
    }

    public function searchPhotoCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    private function _createSearchQueryBuilder($conditions)
    {

        if (isset($conditions['title'])) {
            $conditions['titleLike'] = "%{$conditions['title']}%";
            unset($conditions['title']);
        }

        if (isset($conditions['tagId'])) {
            $tagId = (int) $conditions['tagId'];
            if (!empty($tagId)) {
              $conditions['tagsLike'] = "%|{$conditions['tagId']}|%";
              
            }
           unset($conditions['tagId']);
        }

        if (isset($conditions['id'])) {
            $tagId = (int) $conditions['id'];
            if (!empty($tagId)) {
              $conditions['photoid'] = $tagId;    
            }
            unset($conditions['id']);
        }

        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'photo_group')
            ->andWhere('id = :photoid')
            ->andWhere('name LIKE :titleLike')
            ->andWhere('userId = :userId')
            ->andWhere('tagIds LIKE :tagsLike');

    }

    public function addPhoto($activity)
    {
        $affected = $this->getConnection()->insert($this->table, $activity);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert Photo error.');
        }
        return $this->getPhoto($this->getConnection()->lastInsertId());
    }

    public function updatePhoto($id, $fields)
    {
        $count = $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $count>0?$this->getPhoto($id):null;
    }

    public function deletePhoto($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->executeUpdate($sql, array($id));
    }
}