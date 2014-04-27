<?php

namespace Topxia\Service\Ad\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Ad\Dao\SettingDao;
use Topxia\Common\DaoException;
use PDO;

class SettingDaoImpl extends BaseDao implements SettingDao
{
    protected $table = 'ad_setting';

    public function getSetting($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findSettingByTargetUrl($targetUrl)
    {
        $sql = "SELECT * FROM {$this->table} WHERE targetUrl = ? LIMIT 1";
        
        return $this->getConnection()->fetchAssoc($sql, array($targetUrl))? : null;
    }

    public function findSettingsByIds(array $ids)
    {
        if(empty($ids)){ return array(); }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function searchSettings($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createSettingQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array();
    }

    public function searchSettingCount($conditions)
    {
        $builder = $this->createSettingQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    private function createSettingQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions);       

        return  $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'ad_setting')
            ->andWhere('userId = :userId')

            ->andWhere('createdmTookeen = :createdmTookeen')
            
            ->andWhere('loginmTookeen = :loginmTookeen');
    }

    public function addSetting($guest)
    {
        $affected = $this->getConnection()->insert($this->table, $guest);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert guest error.');
        }
        return $this->getSetting($this->getConnection()->lastInsertId());
    }

    public function updateSetting($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getSetting($id);
    }

    public function waveCounterById($id, $name, $number){

    }

    public function clearCounterById($id, $name){

    }

    

}