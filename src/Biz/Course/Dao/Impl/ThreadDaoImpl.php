<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\ThreadDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ThreadDaoImpl extends GeneralDaoImpl implements ThreadDao
{
    protected $table = 'course_thread';

    public function deleteByCourseId($courseId)
    {
        return $this->db()->delete($this->table(), array('courseId' => $courseId));
    }

    protected function createQueryBuilder($conditions)
    {
        if (isset($conditions['title'])) {
            $conditions['title'] = "%{$conditions['title']}%";
        }

        if (isset($conditions['content'])) {
            $conditions['content'] = "%{$conditions['content']}%";
        }

        return parent::createQueryBuilder($conditions);
    }

    public function findLatestThreadsByType($type, $start, $limit)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE type = ? ORDER BY createdTime DESC";
        $sql = $this->sql($sql, array(), $start, $limit);

        return $this->db()->fetchAll($sql, array($type)) ?: array();
    }

    public function findEliteThreadsByType($type, $status, $start, $limit)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE type = ? AND isElite = ? ORDER BY createdTime";
        $sql = $this->sql($sql, array(), $start, $limit);

        return $this->db()->fetchAll($sql, array($type, $status)) ?: array();
    }

    public function findThreadsByCourseId($courseId, $orderBy, $start, $limit)
    {
        $orderBy = implode(' ', $orderBy);
        $sql = "SELECT * FROM {$this->table} WHERE courseId = ? ORDER BY {$orderBy}";
        $sql = $this->sql($sql, array(), $start, $limit);

        return $this->db()->fetchAll($sql, array($courseId)) ?: array();
    }

    public function findThreadsByCourseIdAndType($courseId, $type, $orderBy, $start, $limit)
    {
        $orderBy = implode(' ', $orderBy);
        $sql = "SELECT * FROM {$this->table} WHERE courseId = ? AND type = ? ORDER BY {$orderBy} ";
        $sql = $this->sql($sql, array(), $start, $limit);

        return $this->db()->fetchAll($sql, array($courseId, $type)) ?: array();
    }

    public function findThreadIds($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('id');

        return $builder->execute()->fetchAll(0) ?: array();
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'serializes' => array(),
            'orderbys' => array('isStick', 'latestPostTime', 'createdTime', 'latestPostTime', 'hitNum', 'updatedTime'),
            'conditions' => array(
                'updatedTime >= :updatedTime_GE',
                'courseId = :courseId',
                'courseSetId = :courseSetId',
                'courseSetId IN (:courseSetIds) ',
                'taskId = :taskId',
                'userId = :userId',
                'type = :type',
                'type IN (:types)',
                'isStick = :isStick',
                'isElite = :isElite',
                'postNum = :postNum',
                'postNum > :postNumLargerThan',
                'title LIKE :title',
                'content LIKE :content',
                'courseId IN (:courseIds)',
                'id IN (:ids)',
                'videoId = :videoId',
                'private = :private',
                'videoAskTime >= :videoAskTime_GE',
                'videoAskTime < :videoAskTime_LE',
                'createdTime >= :startCreatedTime',
                'createdTime < :endCreatedTime',
            ),
        );
    }
}
