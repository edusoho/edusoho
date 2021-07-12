<?php

namespace Biz\WrongBook\Dao\Impl;

use Biz\WrongBook\Dao\WrongQuestionCollectDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class WrongQuestionCollectDaoImpl extends AdvancedDaoImpl implements WrongQuestionCollectDao
{
    protected $table = 'biz_wrong_question_collect';

    public function getCollectBYPoolIdAndItemId($poolId, $itemId)
    {
        return $this->getByFields(['pool_id' => $poolId, 'item_id' => $itemId]);
    }

    public function getCollectBYPoolId($poolId)
    {
        return $this->findByFields(['pool_id' => $poolId, 'status' => 'wrong']);
    }
    public function getCollectIdsBYPoolIds($poolIds)
    {
        $builder = $this->createQueryBuilder(['pool_ids'=>$poolIds])
            ->select('id');
        return $builder->execute()->fetchAll();
    }
    public function deleteCollectByPoolIds($poolIds)
    {
        return $this->batchDelete(['pool_ids' => $poolIds]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['created_time', 'updated_time'],
            'conditions' => [
                'id = :id',
                'pool_id = :pool_id',
                'pool_id IN (:pool_ids)',
                'item_id = :item_id',
                'created_time = :created_time',
                'status = :status',
            ],
            'orderbys' => ['id', 'created_time', 'wrong_times', 'last_submit_time'],
        ];
    }
}
