<?php
namespace Biz\Announcement\Dao\Impl;



use Biz\Announcement\Dao\AnnouncementDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class AnnouncementDaoImpl extends GeneralDaoImpl implements AnnouncementDao
{
    protected $table = 'announcement';

    public function declares()
    {
        return array(
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
            $conditions['likeOrgCode'] = "%{$conditions['likeOrgCode']}%";
            unset($conditions['orgCode']);
        }

        return parent::_createQueryBuilder($conditions);
    }
}
