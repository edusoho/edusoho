<?php
namespace Biz\Announcement\Dao\Impl;



use Biz\Announcement\Dao\AnnouncementDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class AnnouncementDaoImpl extends GeneralDaoImpl implements AnnouncementDao
{
    protected $table = 'announcement';

    public function deleteByTargetIdAndTargetType($targetId, $targetType)
    {
        return $this->db()->delete($this->table(), array('targetId' => $targetId, 'targetType' => $targetType));
    }

    public function declares()
    {
        return array(
            'timestamps' => array(
                'createdTime', 'updatedTime'
            ),
            'orderbys' => array(
                'createdTime'
            ),
            'conditions' => array(
                'targetType = :targetType',
                'targetId = :targetId',
                'targetId IN (:targetIds)',
                'startTime <=:startTime',
                'endTime >=:endTime',
                'orgCode =:orgCode',
                'orgCode LIKE :likeOrgCode',
                'copyId = :copyId',
                'userId =:userId',
            )
        );
    }

    protected function _createQueryBuilder($conditions)
    {
        if (isset($conditions['likeOrgCode'])) {
            $conditions['likeOrgCode'] = $conditions['likeOrgCode'] . '%';
            unset($conditions['orgCode']);
        }

        return parent::_createQueryBuilder($conditions);
    }
}
