<?php

namespace Topxia\Service\MyGroup\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\MyGroup\Dao\ThreadDao;

class ThreadDaoImpl extends BaseDao implements ThreadDao {

    protected $table = 'groups_thread';
    private $serializeFields = array(
        'tagIds' => 'json',
    );
    public function addThread($info){
    	$info = $this->createSerializer()->serialize($info, $this->serializeFields);
        $affected = $this->getConnection()->insert($this->table, $info);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert Article error.');
        }

        return $this->getConnection()->lastInsertId();
    }
    public function searchThread($id,$strat,$limit,$sort){
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT user.smallAvatar as logo,user.nickname,user.id as userid,groups_thread.*  FROM {$this->table},user where  groups_thread.groupId=? and groups_thread.memberId=user.id ORDER BY {$sort} LIMIT $start,$limit";
        $Threadinfo=$this->getConnection()->fetchAll($sql, array($id)) ? : array();
        return $Threadinfo;

    }
}