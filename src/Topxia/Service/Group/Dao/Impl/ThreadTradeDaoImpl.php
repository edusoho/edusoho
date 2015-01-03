<?php
namespace Topxia\Service\Group\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Group\Dao\ThreadTradeDao;

class ThreadTradeDaoImpl extends BaseDao implements ThreadTradeDao
{

    protected $table = 'groups_thread_trade';

    private $serializeFields = array(
        'tagIds' => 'json',
    );

    public function getTrade($id)
    {
        $sql = "SELECT * FROM {$this->table} where id=? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addTrade($log)
    {
        $log = $this->createSerializer()->serialize($log, $this->serializeFields);

        $affected = $this->getConnection()->insert($this->table, $log);
        if ($affected <= 0) {

            throw $this->createDaoException('Insert ThreadTrade error.');
        }

        return $this->getTrade($this->getConnection()->lastInsertId());
    }

    public function getTradeByUserIdAndThreadId($userId,$threadId)
    {
        $sql = "SELECT * FROM {$this->table} where threadId=? and userId=? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($threadId,$userId)) ? : null;
    }

    public function getTradeByUserIdAndGoodsId($userId,$goodsId)
    {
        $sql = "SELECT * FROM {$this->table} where goodsId=? and userId=? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($goodsId,$userId)) ? : null; 
    }

}