<?php

namespace Biz\Live\Dao\Impl;

use Biz\Live\Dao\LiveStatisticsDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class LiveStatisticsDaoImpl extends AdvancedDaoImpl implements LiveStatisticsDao
{
    protected $table = 'live_statistics';

    public function getByLiveIdAndType($liveId, $type)
    {
        return $this->getByFields(array('liveId' => $liveId, 'type' => $type));
    }

    public function findByLiveIdsAndType(array $liveIds, $type)
    {
        $builder = $this->createQueryBuilder(array('liveIds' => $liveIds, 'type' => $type))
            ->select('*');

        return $builder->execute()->fetchAll();
    }

    public function declares()
    {
        return array(
            'serializes' => array('data' => 'json'),
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('createdTime'),
            'conditions' => array(
                'liveId = :liveId',
                'liveId IN (:liveIds)',
                'type = :type',
            ),
        );
    }
}
