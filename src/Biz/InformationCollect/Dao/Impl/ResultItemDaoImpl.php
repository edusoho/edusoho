<?php

namespace Biz\InformationCollect\Dao\Impl;

use Biz\InformationCollect\Dao\ResultItemDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ResultItemDaoImpl extends AdvancedDaoImpl implements ResultItemDao
{
    protected $table = 'information_collect_result_item';

    public function findResultDataByResultIds($resultIds)
    {
        $builder = $this->createQueryBuilder(['resultIds' => $resultIds])
            ->select('resultId, code, labelName, value');

        return $builder->execute()->fetchAll();
    }

    public function findByResultId($resultId)
    {
        return $this->findByFields(['resultId' => $resultId]);
    }

    public function declares()
    {
        return [
            'serializes' => [],
            'orderbys' => [
                'id',
            ],
            'timestamps' => [
                'createdTime',
                'updatedTime',
            ],
            'conditions' => [
                'id = :id',
                'eventId = :eventId',
                'resultId IN (:resultIds)',
                'code = :code',
                'value = :value',
                'value like :likeValue',
            ],
        ];
    }
}
