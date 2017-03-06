<?php

namespace Biz\IM\Dao\Impl;

use Biz\IM\Dao\ConversationDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ConversationDaoImpl extends GeneralDaoImpl implements ConversationDao
{
    protected $table = 'im_conversation';

    public function getByMemberIds(array $memberIds)
    {
        if (empty($memberIds)) {
            return array();
        }
        $ids = '|'.implode('|', $memberIds).'|';

        return $this->getByFields(array('memberIds' => $ids));
    }

    public function getByConvNo($convNo)
    {
        return $this->getByFields(array('no' => $convNo));
    }

    public function getByTargetIdAndTargetType($targetId, $targetType)
    {
        return $this->getByFields(array('targetId' => $targetId, 'targetType' => $targetType));
    }

    public function getByMemberHash($memberHash)
    {
        return $this->getByFields(array('memberHash' => $memberHash));
    }

    public function deleteByTargetIdAndTargetType($targetId, $targetType)
    {
        return $this->db()->delete($this->table, array('targetId' => $targetId, 'targetType' => $targetType));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'orderbys' => array('createdTime'),
            'serializes' => array(
                'memberIds' => 'delimiter',
            ),
            'conditions' => array(
                'targetType IN (:targetTypes)',
                'targetId IN (:targetIds)',
                'convNo = :convNo',
            ),
        );
    }
}
