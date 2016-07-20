<?php
namespace Topxia\Service\Announcement\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Announcement\Dao\AnnouncementDao;

class AnnouncementDaoImpl extends BaseDao implements AnnouncementDao
{
    protected $table = 'announcement';

    public function searchAnnouncements($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $orderBy = $this->checkOrderBy($orderBy, array('createdTime'));

        $builder = $this->createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ?: array();
    }

    public function searchAnnouncementsCount($conditions)
    {
        $builder = $this->createSearchQueryBuilder($conditions)
            ->select('count(id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function getAnnouncement($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function addAnnouncement($fields)
    {
        $affected = $this->getConnection()->insert($this->table, $fields);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert announcement error.');
        }

        return $this->getAnnouncement($this->getConnection()->lastInsertId());
    }

    public function deleteAnnouncement($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->executeUpdate($sql, array($id));
    }

    public function updateAnnouncement($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getAnnouncement($id);
    }

    protected function createSearchQueryBuilder($conditions)
    {
        if (isset($conditions['likeOrgCode'])) {
            $conditions['likeOrgCode'] = $conditions['likeOrgCode'].'%';
            unset($conditions['orgCode']);
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, $this->table)
            ->andWhere("targetType = :targetType")
            ->andWhere("targetId = :targetId")
            ->andWhere("targetId IN (:targetIds)")
            ->andWhere('startTime <=:startTime')
            ->andWhere('endTime >=:endTime')
            ->andWhere('orgCode =:orgCode')
            ->andWhere('orgCode LIKE :likeOrgCode')
            ->andWhere('copyId = :copyId')
            ->andWhere('userId =:userId');

        return $builder;
    }

    protected function filterSort($sort)
    {
        switch ($sort) {
            case 'createdTime':
                $orderBys = array(
                    array('createdTime', 'DESC')
                );
                break;

            default:
                throw $this->createDaoException('参数sort不正确。');
        }

        return $orderBys;
    }
}
