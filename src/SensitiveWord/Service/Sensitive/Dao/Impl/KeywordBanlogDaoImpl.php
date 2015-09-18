<?php
/**
 * Created by PhpStorm.
 * User: edusoho
 * Date: 7/10/15
 * Time: 5:00 PM
 */

namespace SensitiveWord\Service\Sensitive\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use SensitiveWord\Service\Sensitive\Dao\KeywordBanlogDao;

class KeywordBanlogDaoImpl extends BaseDao implements KeywordBanlogDao {

    protected $table = 'keyword_banlog';

    public function addBanlog(array $fields)
    {
        $affected = $this->getConnection()->insert($this->table, $fields);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert keyword banlog error.');
        }
        return $this->getBanlog($this->getConnection()->lastInsertId());
    }

    public function getBanlog($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }


    public function searchBanlogs($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createLogQueryBuilder($conditions)
            ->select('*')
            ->from($this->table, $this->table);
        $builder->addOrderBy($orderBy[0], $orderBy[1]);

        $builder->setFirstResult($start)->setMaxResults($limit);    
        return $builder->execute()->fetchAll() ? : array();
    }

    public function searchBanlogsCount($conditions)
    {
        $builder = $this->createLogQueryBuilder($conditions)
            ->select('count(`id`) AS count')
            ->from($this->table, $this->table);
        return $builder->execute()->fetchColumn(0);
    }

    protected function createLogQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions);
        return $this->createDynamicQueryBuilder($conditions)
            ->andWhere('keywordId = :keywordId');
    }

}