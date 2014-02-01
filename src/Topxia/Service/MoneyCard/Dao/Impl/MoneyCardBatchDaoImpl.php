<?php
namespace Topxia\Service\MoneyCard\Dao\Impl;

use Topxia\Service\Common\BaseDao;

class MoneyCardBatchDaoImpl extends BaseDao
{
	protected $table = 'money_card_batch';

	public function getBatch ($id)
    {
		$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";

        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}

	public function searchBatchs($conditions, $orderBy, $start, $limit)
    {
        $this->checkOrderBy($orderBy, array('id','createdTime'));

        $this->filterStartLimit($start, $limit);
        $builder = $this->createBatchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ? : array();
    }

    public function searchBatchsCount($conditions)
    {
        $builder = $this->createBatchQueryBuilder($conditions)
            ->select('COUNT(id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function addBatch ($batch)
    {
        $affected = $this->getConnection()->insert($this->table, $batch);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert moneyCard error.');
        }

        return $this->getBatch($this->getConnection()->lastInsertId());
    }

    public function updateBatch ($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));

        return $this->getBatch($id);
    }

    public function deleteBatch ($id)
    {
        $this->getConnection()->delete($this->table,array('id' => $id));
    }

    private function createBatchQueryBuilder($conditions)
    {
        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'batch');
    }

}