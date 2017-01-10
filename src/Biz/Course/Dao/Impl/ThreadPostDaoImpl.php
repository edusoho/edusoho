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
        $builder = $this->_createQueryBuilder($conditions)
            ->select('COUNT(id)');

        if (!empty($groupBy)) {
            $builder->addGroupBy($groupBy);
        }

        // return $builder->execute()->fetchColumn(0);
        // 源代码可能有问题，groupBy没有效果，现修改如下：
        return array_column($builder->execute()->fetchAll(), 'COUNT(id)');
    }

    public function deleteByThreadId($threadId)
    {
        $sql = "DELETE FROM {$this->table} WHERE threadId = ?";
        return $this->db()->executeUpdate($sql, array($threadId));
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
            'createdTime' => array('createdTime'),
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
