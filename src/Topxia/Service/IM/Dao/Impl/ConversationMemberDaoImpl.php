<?php
namespace Topxia\Service\IM\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\IM\Dao\ConversationMemberDao;

class ConversationMemberDaoImpl extends BaseDao implements ConversationMemberDao
{
    protected $table = 'im_member';

    public function getMember($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} where id=? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        }

        );
    }

    public function getMemberByConvNoAndUserId($convNo, $userId)
    {
        $that = $this;

        return $this->fetchCached("convNo:{$convNo}:userId:{$userId}", $convNo, $userId, function ($convNo, $userId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} where convNo=? AND userId=? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($convNo, $userId)) ?: null;
        }

        );
    }

    public function findMembersByConvNo($convNo)
    {
        $that = $this;

        return $this->fetchCached("convNo:{$convNo}", $convNo, function ($convNo) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} where convNo = ?";
            return $that->getConnection()->fetchAll($sql, array($convNo));
        }

        );
    }

    public function findMembersByUserIdAndTargetType($userId, $targetType)
    {
        $that = $this;

        return $this->fetchCached("userId:{$userId}:targetType:{$targetType}", $userId, $targetType, function ($userId, $targetType) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} where userId = ? AND targetType= ? ";
            return $that->getConnection()->fetchAll($sql, array($userId, $targetType));
        }

        );
    }

    public function addMember($member)
    {
        $affected = $this->getConnection()->insert($this->table, $member);
        $this->clearCached();

        if ($affected <= 0) {
            throw $this->createDaoException('Insert Conversation error.');
        }

        return $this->getMember($this->getConnection()->lastInsertId());
    }

    public function deleteMember($id)
    {
        $result = $this->getConnection()->delete($this->table, array('id' => $id));
        $this->clearCached();
        return $result;
    }

    public function deleteMemberByConvNoAndUserId($convNo, $userId)
    {
        $result = $this->getConnection()->delete($this->table, array('convNo' => $convNo, 'userId' => $userId));
        $this->clearCached();
        return $result;
    }

    public function deleteMembersByTargetIdAndTargetType($targetId, $targetType)
    {
        $result = $this->getConnection()->delete($this->table, array('targetId' => $targetId, 'targetType' => $targetType));
        $this->clearCached();
        return $result;
    }

    public function searchMembers($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $orderBy = $this->checkOrderBy($orderBy, array('createdTime'));

        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ?: array();
    }

    public function searchMemberCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)

            ->from($this->table, $this->table)
            ->andWhere('targetType IN (:targetTypes)')
            ->andWhere('targetType = :targetType')
            ->andWhere('targetId = :targetId')
            ->andWhere('targetId IN (:targetIds)')
            ->andWhere('userId = :userId')
            ->andWhere('convNo = :convNo');

        return $builder;
    }
}
