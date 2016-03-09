<?php

namespace Topxia\Service\CloudData\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\CloudData\Dao\CloudDataDao;

class CloudDataDaoImpl extends BaseDao implements CloudDataDao
{
    protected $table = 'cloud_data';

    private $serializeFields = array(
        'body' => 'json'
    );

    public function getCloudData($id)
    {
        $sql    = "SELECT * FROM {$this->getTable()} WHERE id = ? LIMIT 1";
        $result = $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        return $this->createSerializer()->unserialize($result, $this->getSerializeFields());
    }

    public function add($fields)
    {
        $fields['createdTime'] = time();
        $fields['updatedTime'] = $fields['createdTime'];
        $fields                = $this->createSerializer()->serialize($fields, $this->serializeFields);

        $affected = $this->getConnection()->insert($this->table, $fields);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert cloud_data error.');
        }

        return $this->getCloudData($this->getConnection()->lastInsertId());
    }
}
