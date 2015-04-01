<?php
namespace Custom\Service\Group\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Custom\Service\Group\Dao\GroupRecommendDao;

class GroupRecommendDaoImpl extends BaseDao implements GroupRecommendDao
{

    protected $table = 'groups_recommend';

    public function getGroupRecommend($id)
    {
        $sql = "SELECT * FROM {$this->table} where id=? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }


    public function deleteGroupRecommend($id){
        $sql = " DELETE FROM  {$this->table}   where groupID=? ";
        return $this->getConnection()->executeQuery($sql, array($id));
    }

    public function addGroupRecommend($group)
    {
        $affected = $this->getConnection()->insert($this->table, $group);
        if ($affected <= 0) {

            throw $this->createDaoException('Insert Group error.');
        }

        return $this->getGroupRecommend($this->getConnection()->lastInsertId());
    }

   public function getRecommendByGroupId(array $groupIds){
        if(empty($groupIds)){ return array(); }
        $marks = str_repeat('?,', count($groupIds) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE groupID IN ({$marks});";
        
        // var_dump($this->getConnection()->fetchAll($sql, $groupIds));
        // exit();
        return $this->getConnection()->fetchAll($sql, $groupIds);
    }
    
	public function getRecommendList($count)
	{
		$sql = "SELECT * FROM {$this->table} ORDER BY seq ASC limit {$count}";
		return $this->getConnection()->fetchAll($sql);
	}


     

}