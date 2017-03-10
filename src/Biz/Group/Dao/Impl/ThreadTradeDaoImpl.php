<?php

namespace Biz\Group\Dao\Impl;

use Biz\Group\Dao\ThreadTradeDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ThreadTradeDaoImpl extends GeneralDaoImpl implements ThreadTradeDao
{
    protected $table = 'groups_thread_trade';

    public function getByUserIdAndThreadId($userId, $threadId)
    {
        return $this->getByFields(array('userId' => $userId, 'threadId' => $threadId));
    }

    public function getByUserIdAndGoodsId($userId, $goodsId)
    {
        return $this->getByFields(array('goodsId' => $goodsId, 'userId' => $userId));
    }

    public function declares()
    {
        return array(
            'serializes' => array(
                'tagIds' => 'json',
            ),
            'conditions' => array(
                'userId = :userId',
                'threadId = :threadId',
                'goodsId = :goodsId',
            ),
        );
    }
}
