<?php
namespace Topxia\Service\RefererLog\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\RefererLog\Dao\OrderRefererDao;

class OrderRefererDaoImpl extends BaseDao implements OrderRefererDao
{
    protected $table = 'order_referer';

    private $serializeFields = array(
        'data' => 'phpserialize'
    );

    public function getOrderReferer($id)
    {
        $sql   = "SELECT * FROM {$this->getTable()} WHERE id = ? LIMIT 1";
        $token = $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        return $token ? $this->createSerializer()->unserialize($token, $this->serializeFields) : null;
    }

    public function getOrderRefererByUv($uv)
    {
        $sql   = "SELECT * FROM {$this->getTable()} WHERE uv = ?  AND expiredTime >= ? LIMIT 1";
        $token = $this->getConnection()->fetchAssoc($sql, array($uv, time())) ?: null;
        return $token ? $this->createSerializer()->unserialize($token, $this->serializeFields) : null;
    }

    public function getOrderRefererLikeByOrderId($orderId)
    {
        $likeOrderIds = '%|'.$orderId.'|%';
        $sql          = "SELECT * FROM {$this->getTable()} WHERE orderIds like ?  LIMIT 1";
        $token        = $this->getConnection()->fetchAssoc($sql, array($likeOrderIds)) ?: null;
        return $token ? $this->createSerializer()->unserialize($token, $this->serializeFields) : null;
    }

    public function createOrderReferer($token)
    {
        $token    = $this->createSerializer()->serialize($token, $this->serializeFields);
        $affected = $this->getConnection()->insert($this->getTable(), $token);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert referer_order_token error.');
        }

        return $this->getOrderReferer($this->getConnection()->lastInsertId());
    }

    public function updateOrderReferer($id, $fields)
    {
        $fields = $this->createSerializer()->serialize($fields, $this->serializeFields);

        $this->getConnection()->update($this->getTable(), $fields, array('id' => $id));
        return $this->getOrderReferer($id);
    }
}
