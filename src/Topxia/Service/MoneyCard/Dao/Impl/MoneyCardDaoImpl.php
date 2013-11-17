<?php
namespace Topxia\Service\MoneyCard\Dao\Impl;

use Topxia\Service\Common\BaseDao;

class MoneyCardDaoImpl extends BaseDao {

	protected $table = 'money_card';

	public function getMoneyCard($id) {

		$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";

        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}

	public function searchMoneyCards($conditions, $orderBy, $start, $limit) {

        $this->filterStartLimit($start, $limit);
        $builder = $this->createMoneyCardQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ? : array();
    }

    public function searchMoneyCardsAll($conditions, $orderBy) {

        $builder = $this->createMoneyCardQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1]);

        return $builder->execute()->fetchAll() ? : array();
    }

    public function searchMoneyCardsCount($conditions) {

        $builder = $this->createMoneyCardQueryBuilder($conditions)
            ->select('COUNT(id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function addMoneyCard ($moneyCards, $number) {

        if(empty($moneyCards)){ return array(); }

        $sql = "INSERT INTO $this->table (cardId, password, validTime, rechargeStatus, batchId)     VALUE ";
        for ($i=0; $i < $number; $i++) {
            $sql .= "(?, ?, ?, ?, ?),";
        }

        $sql = substr($sql, 0, -1);

        return $this->getConnection()->executeUpdate($sql, $moneyCards);
    }

    public function isCardIdAvaliable ($moneyCardIds) {

        $sql = "select COUNT(id) from ".$this->table." where cardId in (".$moneyCardIds.")";

        $result = $this->getConnection()->fetchAll($sql);

        return $result[0]["COUNT(id)"] == 0 ? true : false;
    }

    public function updateMoneyCard ($id, $fields) {

        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getMoneyCard($id);
    }

    public function deleteMoneyCard ($id) {

        $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function updateBatch ($id, $fields) {

        $this->getConnection()->update($this->table, $fields, array('batchId' => $id));
    }

    public function deleteBatch ($id) {

        $this->getConnection()->delete($this->table, array('batchId' => $id));
    }

    private function createMoneyCardQueryBuilder($conditions) {

        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'money_card')
            ->andWhere('promoted = :promoted')
            ->andWhere('cardId = :cardId')
            ->andWhere('validTime = :validTime')
            ->andWhere('batchId = :batchId');
    }
}