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

    public function searchCloudDatas($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
                        ->select('*')
                        ->orderBy($orderBy[0], $orderBy[1])
                        ->setFirstResult($start)
                        ->setMaxResults($limit);

        $result = $builder->execute()->fetchAll() ?: array();
        return $this->createSerializer()->unserializes($result, $this->serializeFields);
    }

    public function searchCloudDataCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
                        ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
                        ->from($this->table, 'cloud_data');

        return $builder;
    }

    public function getCloudData($id)
    {
        $sql    = "SELECT * FROM {$this->getTable()} WHERE id = ? LIMIT 1";
        $result = $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        return $this->createSerializer()->unserialize($result, $this->serializeFields);
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

    public function deleteCloudData($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }
}
