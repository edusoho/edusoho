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

    public function addGroupRecommend($group)
    {
        $group = $this->createSerializer()->serialize($group, $this->serializeFields);

        $affected = $this->getConnection()->insert($this->table, $group);
        if ($affected <= 0) {

            throw $this->createDaoException('Insert Group error.');
        }

        return $this->getGroupRecommend($this->getConnection()->lastInsertId());
    }

     

}