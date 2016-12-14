<?php
namespace Biz\User\Dao\Impl;

use Biz\User\Dao\StatusDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class StatusDaoImpl extends GeneralDaoImpl implements StatusDao
{
    protected $table = 'status';

    private $serializeFields = array(
        'properties' => 'json'
    );

    public function searchByUserIds($userIds, $start, $limit)
    {
        if (empty($userIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($userIds) - 1).'?';
        $sql   = "SELECT * FROM {$this->table} WHERE userId IN ({$marks});";
        //FIXME 这样写，没法通过declares 处理serialize
        return $this->db()->fetchAll($sql, $userIds);
    }

    public function countByUserIds($userIds)
    {
        if (empty($userIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($userIds) - 1).'?';
        $sql   = "SELECT COUNT(*) FROM {$this->table} WHERE userId IN ({$marks});";

        return $this->db()->fetchColumn($sql, $userIds);
    }

    public function searchStatuses($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $this->checkOrderBy($orderBy, array('createdTime'));

        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->orderBy($orderBy[0], $orderBy[1]);

        $statuses = $builder->execute()->fetchAll() ?: array();

        return $this->createSerializer()->unserializes($statuses, $this->serializeFields);
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, $this->table)
            ->andWhere('courseId = :courseId')
            ->andWhere('courseId IN ( :courseIds )')
            ->andWhere('courseId IN ( :classroomCourseIds ) OR classroomId = :classroomId')
            ->andWhere('classroomId = :onlyClassroomId')
            ->andWhere('objectType = :objectType')
            ->andWhere('objectId = :objectId')
            ->andWhere('userId = :userId')
            ->andWhere('private = :private');
    }

    public function deleteByUserIdAndTypeAndObject($userId, $type, $objectType, $objectId)
    {
        return $this->db()->delete($this->table, array(
            'userId'     => $userId,
            'type'       => $type,
            'objectType' => $objectType,
            'objectId'   => $objectId
        ));
    }

    public function deleteByCourseIdAndTypeAndObject($courseId, $type, $objectType, $objectId)
    {
        return $this->db()->delete($this->table, array(
            'courseId'   => $courseId,
            'type'       => $type,
            'objectType' => $objectType,
            'objectId'   => $objectId
        ));
    }

    public function findByCourseId($courseId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE courseId = ?";
        return $this->db()->fetchAll($sql, array($courseId));
    }

    public function declares()
    {
        return array(
            'serializes' => array(
                'properties' => 'json'
            ),
            'orderbys'   => array('createdTime')
        );
    }
}
