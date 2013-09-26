<?php
namespace Topxia\Service\Photo\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Photo\Dao\PhotoCommentDao;

class PhotoCommentDaoImpl extends BaseDao implements PhotoCommentDao
{
    protected $table = 'photo_comment';

    public function getComment($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }
    

    public function findCommentsByFileId($fileId, $orderBy, $start, $limit)
    {
        $builder = $this->_createSearchQueryBuilder($fileId)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array(); 
    }

    public function searchCommentCount($conditions)
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

        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'course')
            ->andWhere('imgId = :id')
            ->andWhere('title LIKE :titleLike')
            ->andWhere('userId = :userId');
    }

    public function addComment($thread)
    {
        $affected = $this->getConnection()->insert($this->table, $thread);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert course error.');
        }
        return $this->getComment($this->getConnection()->lastInsertId());
    }

    public function updateComment($id, $fields)
    {
        $count = $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $count>0?$this->getComment($id):null;
    }

    public function deleteComment($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->executeUpdate($sql, array($id));
    }

    public function deleteCommentByIds(array $ids){
        $sql = "DELETE FROM {$this->table} WHERE id in ? LIMIT 1";
        return $this->getConnection()->executeUpdate($sql, array($id));
    }

}