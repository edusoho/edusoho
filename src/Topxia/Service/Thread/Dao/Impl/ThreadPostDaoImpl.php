<?php

namespace Topxia\Service\Thread\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Thread\Dao\ThreadPostDao;

class ThreadPostDaoImpl extends BaseDao implements ThreadPostDao
{

	protected $table = 'thread_post';
    private $serializeFields = array(
        'tagIds' => 'json',
    );
	public function getPost($id)
	{
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}

	public function findPostsByThreadId($threadId, $orderBy, $start, $limit)
	{
        $this->filterStartLimit($start, $limit);
        //@todo: fixed me.
		$orderBy = join (' ', $orderBy);
        $sql = "SELECT * FROM {$this->table} WHERE threadId = ? ORDER BY {$orderBy} LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($threadId)) ? : array();
	}

	public function getPostCountByThreadId($threadId)
	{
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE threadId = ?";
        return $this->getConnection()->fetchColumn($sql, array($threadId));
	}

	public function getPostCountByuserIdAndThreadId($userId,$threadId)
	{
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE userId = ? AND threadId = ?";
        return $this->getConnection()->fetchColumn($sql, array($userId,$threadId));
	}

	public function findPostsByThreadIdAndIsElite($threadId, $isElite, $start, $limit)
	{
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE threadId = ? AND isElite = ? ORDER BY createdTime ASC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($threadId,  $isElite)) ? : array();
	}

	public function searchPostsCount($conditions)
	{
	    $builder = $this->_createThreadSearchBuilder($conditions)
	                     ->select('count(id)');

	    return $builder->execute()->fetchColumn(0); 
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

	public function updatePost($id, array $fields)
	{
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getPost($id);
	}

	public function deletePost($id)
	{
		return $this->getConnection()->delete($this->table, array('id' => $id));
	}

	public function deletePostsByThreadId($threadId)
	{	
        $sql ="DELETE FROM {$this->table} WHERE threadId = ?";
        return $this->getConnection()->executeUpdate($sql, array($threadId));
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

	private function _createThreadSearchBuilder($conditions)
	{
	    $builder = $this->createDynamicQueryBuilder($conditions)
	        ->from($this->table,$this->table)
	        ->andWhere('userId = :userId')
	        ->andWhere('id < :id')
	        ->andWhere('parentId = :parentId')
	        ->andWhere('threadId = :threadId');
	    return $builder;
	}

}