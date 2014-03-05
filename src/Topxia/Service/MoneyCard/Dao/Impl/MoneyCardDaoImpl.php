<?php
namespace Topxia\Service\MoneyCard\Dao\Impl;

use Topxia\Service\Common\BaseDao;

class MoneyCardDaoImpl extends BaseDao
{
	protected $table = 'money_card';

	public function getMoneyCard($id)
    {
		$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";

        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}

	public function searchMoneyCards($conditions, $orderBy, $start, $limit)
    {
        $this->checkOrderBy($orderBy, array('id','createdTime'));

        $this->filterStartLimit($start, $limit);
        $builder = $this->createMoneyCardQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ? : array();
    }

    public function searchMoneyCardsCount($conditions)
    {
        $builder = $this->createMoneyCardQueryBuilder($conditions)
            ->select('COUNT(id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function addMoneyCard ($moneyCards)
    {
        if(empty($moneyCards)){ return array(); }

        $moneyCardsForSQL = array();
        foreach ($moneyCards as $value) {
            $moneyCardsForSQL = array_merge($moneyCardsForSQL, array_values($value));
        }

        $sql = "INSERT INTO $this->table (cardId, password, deadline, cardStatus, batchId)     VALUE ";
        for ($i=0; $i < count($moneyCards); $i++) {
            $sql .= "(?, ?, ?, ?, ?),";
        }

        $sql = substr($sql, 0, -1);

        return $this->getConnection()->executeUpdate($sql, $moneyCardsForSQL);
    }

    public function isCardIdAvaliable ($moneyCardIds)
    {
        $str = implode(',', array_map(function($value){ return "'".$value."'"; },
            array_keys($moneyCardIds)));

        $sql = "select COUNT(id) from ".$this->table." where cardId in (".$str.")";

        $result = $this->getConnection()->fetchAll($sql);

        return $result[0]["COUNT(id)"] == 0 ? true : false;
    }

    public function updateMoneyCard ($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getMoneyCard($id);
    }

    public function deleteMoneyCard ($id)
    {
        $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function updateBatchByCardStatus ($identifier, $fields)
    {
        $this->getConnection()->update($this->table, $fields, $identifier);
    }

    public function deleteBatchByCardStatus ($fields)
    {
        $sql = "DELETE FROM ".$this->table." WHERE batchId = ? AND cardStatus != ?";
        $this->getConnection()->executeUpdate($sql, $fields);
    }

    private function createMoneyCardQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions);
        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'money_card')
            ->andWhere('cardId = :cardId')
            ->andWhere('deadline = :deadline')
            ->andWhere('batchId = :batchId');
    }

}