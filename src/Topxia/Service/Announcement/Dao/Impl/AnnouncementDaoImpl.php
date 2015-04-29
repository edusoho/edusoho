<?php
namespace Topxia\Service\Announcement\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Announcement\Dao\AnnouncementDao;

class AnnouncementDaoImpl extends BaseDao implements AnnouncementDao
{   
    protected $table = 'announcement';

    public function getAnnouncement($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function createAnnouncement($announcement)
    {
        $affected = $this->getConnection()->insert($this->table, $announcement);
        if ($affected <= 0) {

            throw $this->createDaoException('Insert announcement error.');
        }

        return $this->getAnnouncement($this->getConnection()->lastInsertId());
    }

    public function updateAnnouncement($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));

        return $this->getAnnouncement($id);
    }
    
    public function deleteAnnouncement($id)
    {
        $this->getConnection()->delete($this->table,array('id'=>$id));
    }

    public function searchAnnouncements($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);

        $builder = $this->_createAnnouncementSearchBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->addOrderBy($orderBy[0], $orderBy[1]);
  
        return $builder->execute()->fetchAll() ? : array();  
    }

    public function searchAnnouncementsCount($conditions)
    {        
        $builder = $this->_createAnnouncementSearchBuilder($conditions)
            ->select('count(id)');
  
        return $builder->execute()->fetchColumn(0); 

    }

    private function _createAnnouncementSearchBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table,$this->table)
            ->andWhere('startTime <=:startTime')
            ->andWhere('endTime >=:endTime')
            ->andWhere('userId =:userId');


        return $builder;
    }
}