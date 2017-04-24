<?php

namespace Biz\IM\Dao\Impl;

use Biz\IM\Dao\ConversationMemberDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ConversationMemberDaoImpl extends GeneralDaoImpl implements ConversationMemberDao
{
    protected $table = 'im_member';

    public function getByConvNoAndUserId($convNo, $userId)
    {
        return $this->getByFields(array('convNo' => $convNo, 'userId' => $userId));
    }

    public function findByConvNo($convNo)
    {
        return $this->findByFields(array('convNo' => $convNo));
    }

    public function findByUserIdAndTargetType($userId, $targetType)
    {
        return $this->findByFields(array('userId' => $userId, 'targetType' => $targetType));
    }

    public function deleteByConvNoAndUserId($convNo, $userId)
    {
        return $this->db()->delete($this->table, array('convNo' => $convNo, 'userId' => $userId));
    }

    public function deleteByTargetIdAndTargetType($targetId, $targetType)
    {
        return $this->db()->delete($this->table, array('targetId' => $targetId, 'targetType' => $targetType));
    }

    public function declares()
    {
        return array(
            'orderbys' => array('createdTime'),
            'conditions' => array(
                'targetType IN (:targetTypes)',
                'targetType = :targetType',
                'targetId = :targetId',
                'targetId IN (:targetIds)',
                'userId = :userId',
                'convNo = :convNo',
            ),
        );
    }
}
