<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\ThreadPostDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ThreadPostDaoImpl extends GeneralDaoImpl implements ThreadPostDao
{
    protected $table = 'course_thread_post';

    public function searchByUserIdGroupByThreadId($userId, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createQueryBuilder(array('userId' => $userId))
            ->select('course_thread_post.*')
            ->where('id in (SELECT MAX(id) AS id FROM `course_thread_post` WHERE userId = :userId GROUP BY threadId)')
            ->addOrderBy('id', 'desc')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll();
    }

    public function countGroupByThreadId($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('COUNT(id)')
            ->addGroupBy('threadId');

        return intval($builder->execute()->fetchColumn(0));
    }

    public function deleteByThreadId($threadId)
    {
        $sql = "DELETE FROM {$this->table} WHERE threadId = ?";

        return $this->db()->executeUpdate($sql, array($threadId));
    }

    public function deleteByCourseId($courseId)
    {
        return $this->db()->delete($this->table(), array('courseId' => $courseId));
    }

    public function findThreadIds($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('threadId');

        return $builder->execute()->fetchAll(0) ?: array();
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'orderbys' => array('createdTime'),
            'conditions' => array(
                'updatedTime >= :updatedTime_GE',
                'createdTime >= :createdTime_GE',
                'courseSetId = :courseSetId',
                'courseId = :courseId',
                'courseId IN ( :courseIds)',
                'taskId = :taskId',
                'threadId = :threadId',
                'threadId IN ( :threadIds)',
                'userId = :userId',
                'userId != :exceptedUserId',
                'isElite = :isElite',
                'isRead = :isRead',
                'content LIKE :content',
            ),
        );
    }
}
