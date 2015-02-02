<?php 
namespace Custom\Service\TopLink\Dao\Impl;
use Topxia\Service\Common\BaseDao;
use Custom\Service\TopLink\Dao\TopLinkDao;
class TopLinkDaoImpl extends BaseDao implements TopLinkDao
{
    public function getTopLink($id)
    {
        $sql = "SELECT * FROM {$this->getTablename()} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }
    
    public function searchTopLinks($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createTopLinkQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array();
    }

    public function searchTopLinkCount($conditions)
    {
        $builder = $this->createTopLinkQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function addTopLink($topLink)
    {
        $affected = $this->getConnection()->insert(self::TABLENAME, $topLink);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert top_link error.');
        }
        return $this->getTopLink($this->getConnection()->lastInsertId());
    }

    public function updateTopLink($id,$fields)
    {
        $this->getConnection()->update(self::TABLENAME, $fields, array('id' => $id));
        return $this->getTopLink($id);
    }

    public function deleteTopLink($id)
    {
        return $this->getConnection()->delete(self::TABLENAME, array('id' => $id));
    }
    
    private function createTopLinkQueryBuilder($conditions)
    {
        return  $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, $this->getTablename())
            ->andWhere('name = :name');
    }

    private function getTablename()
    {
        return self::TABLENAME;
    }
}