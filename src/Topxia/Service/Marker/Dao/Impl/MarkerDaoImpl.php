<?php
namespace Topxia\Service\Marker\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Marker\Dao\MarkerDao;

class MarkerDaoImpl extends BaseDao implements MarkerDao
{
    protected $table = 'marker';

    public function getMarker($id)
    {
        $sql = "SELECT * FROM {$this->table} where id=? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function getMarkersByIds($ids)
    {
        if (empty($ids)) {
            return array();
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql   = "SELECT * FROM {$this->table} WHERE id IN ({$marks});";

        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function findMarkersByMediaId($mediaId)
    {
        $sql     = "SELECT * FROM {$this->table} where mediaId = ?";
        $markers = $this->getConnection()->fetchAll($sql, array($mediaId));
        return $markers;
    }

    public function searchMarkers($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);

        $builder = $this->_createMarkerSearchBuilder($conditions)
                        ->select('*')
                        ->setFirstResult($start)
                        ->setMaxResults($limit)
                        ->addOrderBy($orderBy[0], $orderBy[1]);

        return $builder->execute()->fetchAll() ?: array();
    }

    public function updateMarker($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));

        return $this->getMarker($id);
    }

    public function addMarker($marker)
    {
        $affected = $this->getConnection()->insert($this->table, $marker);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert Marker error.');
        }

        return $this->getMarker($this->getConnection()->lastInsertId());
    }

    public function deleteMarker($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    protected function _createMarkerSearchBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
                        ->from($this->table, $this->table)
                        ->andWhere('mediaId=:mediaId')
                        ->andWhere('second = :second');

        return $builder;
    }
}
