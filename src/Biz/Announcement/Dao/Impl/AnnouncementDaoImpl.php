<?php

namespace Biz\Announcement\Dao\Impl;

use Biz\Announcement\Dao\AnnouncementDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class AnnouncementDaoImpl extends GeneralDaoImpl implements AnnouncementDao
{
    protected $table = 'announcement';

    public function deleteByTargetIdAndTargetType($targetId, $targetType)
    {
        return $this->db()->delete($this->table(), ['targetId' => $targetId, 'targetType' => $targetType]);
    }

    public function declares()
    {
        return [
            'timestamps' => [
                'createdTime', 'updatedTime',
            ],
            'orderbys' => [
                'createdTime',
                'startTime',
            ],
            'conditions' => [
                'targetType = :targetType',
                'targetId = :targetId',
                'targetId IN (:targetIds)',
                'startTime <=:startTime',
                'endTime >=:endTime',
                'endTime >=:endTime_GTE',
                'endTime <=:endTime_LTE',
                'startTime >=:startTime_GT',
                'startTime >=:startTime_GTE',
                'startTime <=:startTime_LTE',
                'orgCode =:orgCode',
                'orgCode PRE_LIKE :likeOrgCode',
                'copyId = :copyId',
                'userId =:userId',
            ],
        ];
    }

    protected function createQueryBuilder($conditions)
    {
        if (isset($conditions['likeOrgCode'])) {
            $conditions['likeOrgCode'] = $conditions['likeOrgCode'].'%';
            unset($conditions['orgCode']);
        }

        return parent::createQueryBuilder($conditions);
    }
}
