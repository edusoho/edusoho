<?php
namespace Topxia\Service\Group\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Group\Dao\ThreadBuyHideDao;

class ThreadBuyHideDaoImpl extends BaseDao implements ThreadBuyHideDao
{

    protected $table = 'groups_thread_buy_hide';

    private $serializeFields = array(
        'tagIds' => 'json',
    );

    public function getBuyHide($id)
    {
        $sql = "SELECT * FROM {$this->table} where id=? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addBuyHide($log)
    {
        $log = $this->createSerializer()->serialize($log, $this->serializeFields);

        $affected = $this->getConnection()->insert($this->table, $log);
        if ($affected <= 0) {

            throw $this->createDaoException('Insert ThreadBuyHide error.');
        }

        return $this->getBuyHide($this->getConnection()->lastInsertId());
    }

    public function getbuyHideByUserIdandThreadId($id,$userId)
    {
        $sql = "SELECT * FROM {$this->table} where threadId=? and userId=? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id,$userId)) ? : null;
    }

}