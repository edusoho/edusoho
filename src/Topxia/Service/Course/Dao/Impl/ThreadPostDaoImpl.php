<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\ThreadPostDao;

class ThreadPostDaoImpl extends BaseDao implements ThreadPostDao
{
    protected $table = 'course_thread_post';

    public function getPost($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function findPostsByThreadId($threadId, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $orderBy = join(' ', $orderBy);
        $sql     = "SELECT * FROM {$this->table} WHERE threadId = ? ORDER BY {$orderBy} LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($threadId)) ?: array();
    }

    public function getPostCountByThreadId($threadId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE threadId = ?";
        return $this->getConnection()->fetchColumn($sql, array($threadId));
    }

    public function searchThreadPosts($conditions, $orderBy, $start, $limit, $groupBy = '')
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createThreadPostSearchQueryBuilder($conditions)
                        ->select('*')
                        ->orderBy($orderBy[0], $orderBy[1])
                        ->setFirstResult($start)
                        ->setMaxResults($limit);

        if (!empty($groupBy)) {
            $builder->addGroupBy($groupBy);
        }

        return $builder->execute()->fetchAll() ?: array();
    }

    public function searchThreadPostsCount($conditions, $groupBy = '')
    {
        $builder = $this->createThreadPostSearchQueryBuilder($conditions)
                        ->select('COUNT(id)');

        if (!empty($groupBy)) {
            $builder->addGroupBy($groupBy);
        }

        return $builder->execute()->fetchColumn(0);
    }

    public function getPostCountByuserIdAndThreadId($userId, $threadId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE userId = ? AND threadId = ?";
        return $this->getConnection()->fetchColumn($sql, array($userId, $threadId));
    }

    public function findPostsByThreadIdAndIsElite($threadId, $isElite, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE threadId = ? AND isElite = ? ORDER BY createdTime ASC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($threadId, $isElite)) ?: array();
    }

    public function addPost(array $post)
    {
        $affected = $this->getConnection()->insert($this->table, $post);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert course post error.');
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
        $sql = "DELETE FROM {$this->table} WHERE threadId = ?";
        return $this->getConnection()->executeUpdate($sql, array($threadId));
    }

    protected function createThreadPostSearchQueryBuilder($conditions)
    {
        if (isset($conditions['content'])) {
            $conditions['content'] = "%{$conditions['content']}%";
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
                        ->from($this->table, $this->table)
                        ->andWhere('updatedTime >= :updatedTime_GE')
                        ->andWhere('courseId = :courseId')
                        ->andWhere('lessonId = :lessonId')
                        ->andWhere('threadId = :threadId')
                        ->andWhere('userId = :userId')
                        ->andWhere('isElite = :isElite')
                        ->andWhere('content LIKE :content');

        return $builder;
    }
}
