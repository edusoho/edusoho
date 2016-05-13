<?php
namespace Topxia\Service\IM\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\IM\Dao\MyConversationDao;

class MyConversationDaoImpl extends BaseDao implements MyConversationDao
{
    protected $table = 'im_my_conversation';

    public function getMyConversation($id)
    {
        $sql = "SELECT * FROM {$this->getTable()} where id=? LIMIT 1";
        $myConversation = $this->getConnection()->fetchAssoc($sql, array($id));
        return $myConversation ? : null;
    }

    public function getMyConversationByNo($no)
    {
        $sql = "SELECT * FROM {$this->getTable()} where no=? LIMIT 1";
        $myConversation = $this->getConnection()->fetchAssoc($sql, array($no));
        return $myConversation ? : null;
    }

    public function addMyConversation($myConversation)
    {
        $affected = $this->getConnection()->insert($this->table, $myConversation);
        $this->clearCached();

        if ($affected <= 0) {
            throw $this->createDaoException('Insert MyConversation error.');
        }

        return $this->getMyConversation($this->getConnection()->lastInsertId());
    }

    public function updateMyConversation($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getMyConversation($id);
    }

    public function updateMyConversationByNo($no, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('no' => $no));
        return $this->getMyConversationByNo($no);
    }

    public function searchMyConversations($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $orderBy = $this->checkOrderBy($orderBy, array('updatedTime'));

        $builder = $this->createMyConversationQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ?: array();
    }

    public function searchMyConversationCount($conditions)
    {
        $builder = $this->createMyConversationQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    protected function createMyConversationQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions);

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table)
            ->andWhere('no = :no')
            ->andWhere('userId = :userId');

        return $builder;
    }
}
