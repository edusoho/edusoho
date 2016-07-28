<?php
namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\UpgradeNoticeDao;

class UpgradeNoticeDaoImpl extends BaseDao implements UpgradeNoticeDao
{
    protected $table = 'upgrade_notice';

    public function getNotice($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function getNoticeByUserIdAndVersionAndCode($userId, $version, $code)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? and version = ? and code = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($userId, $version, $code)) ?: null;
    }

    public function addNotice($fields)
    {
        $affected = $this->getConnection()->insert($this->table, $fields);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert upgrade notice error.');
        }

        return $this->getNotice($this->getConnection()->lastInsertId());
    }

    public function updateNotice($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getNotice($id);
    }

    public function deleteStatus($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }
}
