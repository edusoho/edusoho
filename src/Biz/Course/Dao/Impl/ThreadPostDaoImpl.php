<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\ThreadPostDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ThreadPostDaoImpl extends GeneralDaoImpl implements ThreadPostDao
{
    protected $table = 'course_thread_post';

    public function searchByGroup($conditions, $orderBys, $start, $limit, $groupBy = '')
    {
        $builder = $this->_createQueryBuilder($conditions)
            ->select('*');

        foreach ($orderBys ?: array() as $field => $direction) {
            $builder->addOrderBy($field, $direction);
        }

        if (!empty($groupBy)) {
            $builder->addGroupBy($groupBy);
        }

        return $builder->execute()->fetchAll() ?: array();
    }

    public function countByGroup($conditions, $groupBy = '')
    {
        $index = empty($groupBy) ? '' : $groupBy . ',';
        $builder = $this->_createQueryBuilder($conditions)
            ->select("{$index} COUNT(id) AS count");

        if (!empty($groupBy)) {
            $builder->addGroupBy($groupBy);
        }

        return $builder->execute()->fetchAll();
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

    protected function _createQueryBuilder($conditions)
    {
        if (isset($conditions['content'])) {
            $conditions['content'] = "%{$conditions['content']}%";
        }

        return parent::_createQueryBuilder($conditions);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'orderbys'    => array('createdTime'),
            'conditions'  => array(
                'updatedTime >= :updatedTime_GE',
                'courseSetId = :courseSetId',
                'courseId = :courseId',
                'courseId IN ( :courseIds)',
                'taskId = :taskId',
                'threadId = :threadId',
                'userId = :userId',
                'isElite = :isElite',
                'content LIKE :content'
            )
        );
    }
}
