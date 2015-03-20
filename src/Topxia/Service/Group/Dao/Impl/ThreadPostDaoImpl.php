<?php 
namespace Topxia\Service\Group\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Group\Dao\ThreadPostDao;

class ThreadPostDaoImpl extends BaseDao implements ThreadPostDao
{

	protected $table = 'groups_thread_post';
    private $serializeFields = array(
        'tagIds' => 'json',
    );

    public function getPost($id)
    {
        $sql="SELECT * from {$this->table} where id=? LIMIT 1";

        return $this->getConnection()->fetchAssoc($sql,array($id)) ? : null;
    }

    public function addPost($fields)
    {
    	$this->createSerializer()->serialize($fields, $this->serializeFields);

    	$affected = $this->getConnection()->insert($this->table, $fields);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert postThread error.');
        }

        return $this->getPost($this->getConnection()->lastInsertId());
    }


    public function searchPosts($conditions,$orderBy,$start,$limit)
    {
        $this->filterStartLimit($start,$limit);

        $builder=$this->_createThreadSearchBuilder($conditions)
        ->select('*')
        ->setFirstResult($start)
        ->setMaxResults($limit)
        ->orderBy($orderBy[0],$orderBy[1]);
        
        return $builder->execute()->fetchAll() ? : array(); 
    }

  
    public function searchPostsCount($conditions)
    {
        $builder = $this->_createThreadSearchBuilder($conditions)
                         ->select('count(id)');

        return $builder->execute()->fetchColumn(0); 
    }

 
    public function updatePost($id,$fields)
    {
        $this->getConnection()->update($this->table,$fields,array('id'=>$id));

        return $this->getPost($id);
    }

    public function deletePost($id)
    {
        $this->getConnection()->delete($this->table,array('id'=>$id));
    }


    public function deletePostsByThreadId($threadId)
    {
         $this->getConnection()->delete($this->table,array('threadId'=>$threadId));
    }

    private function _createThreadSearchBuilder($conditions)
    {

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table,$this->table)
            ->andWhere('userId = :userId')
            ->andWhere('id < :id')
            ->andWhere('postId = :postId')
            ->andWhere('adopt = :adopt')
            ->andWhere('threadId = :threadId');
        return $builder;
    }

    public function searchPostsThreadIds($conditions,$orderBy,$start,$limit)
    {
        $builder=$this->_createThreadSearchBuilder($conditions)
        ->select('distinct threadId')
        ->setFirstResult($start)
        ->setMaxResults($limit)
        ->orderBy($orderBy[0],$orderBy[1]);

        return $builder->execute()->fetchAll() ? : array(); 
    }

    public function searchPostsThreadIdsCount($conditions)
    {
        $builder = $this->_createThreadSearchBuilder($conditions)
                         ->select('count(distinct threadId)');

        return $builder->execute()->fetchColumn(0); 
    }
}