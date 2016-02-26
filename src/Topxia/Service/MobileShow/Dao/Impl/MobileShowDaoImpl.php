<?php
namespace Topxia\Service\MobileShow\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\MobileShow\Dao\MobileShowDao;

class MobileShowDaoImpl extends BaseDao implements MobileShowDao
{
	protected $table = 'mobile_category';

	public function getMobileShow($id)
    {
        $sql = "SELECT * FROM {$this->table} where id=? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function updateMobileShow($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));

        return $this->getMobileShow($id);
    }

    public function addMobileShow($MobileShow)
    {
        $affected = $this->getConnection()->insert($this->table, $MobileShow);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert MobileShow error.');
        }

        return $this->getMobileShow($this->getConnection()->lastInsertId());
    }

    public function findMobileShowByTitle($title)
    {
        $sql     = "SELECT * FROM {$this->table} where title = ?";
        $mobileShow = $this->getConnection()->fetchAll($sql, array($title));
        return $mobileShow;
    }

    public function deleteMobileShow($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function getAllMobileShows()
    {
        $sql = "SELECT * FROM {$this->table}";
        return $this->getConnection()->fetchAll($sql);
    }
}