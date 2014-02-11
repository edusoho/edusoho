<?php
namespace Topxia\Service\Testpaper\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Testpaper\Dao\TestpaperDao;

class TestpaperDaoImpl extends BaseDao implements TestpaperDao
{
	protected $table = 'testpaper';

    private $serializeFields = array(
            'metas' => 'json'
    );

    public function getTestpaper($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $testpaper = $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
        return $this->createSerializer()->unserialize($testpaper, $this->serializeFields);
    }

    public function findTestpapersByIds(array $ids)
    {
        if(empty($ids)){ 
            return array(); 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function searchTestpapers($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $this->checkOrderBy($orderBy, array('createdTime'));

        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->orderBy($orderBy[0], $orderBy[1]);

        $questions = $builder->execute()->fetchAll() ? : array();

        return $this->createSerializer()->unserializes($questions, $this->serializeFields);
    }

    public function searchTestpapersCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
             ->select('COUNT(id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function addTestpaper($fields)
    {

    }

    public function updateTestpaper($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getTestPaper($id);
    }

    public function findTestpaperByTargets(array $targets)
    {
        if(empty($targets)){ 
            return array(); 
        }
        $marks = str_repeat('?,', count($targets) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE target IN ({$marks});";
        $results = $this->getConnection()->fetchAll($sql, $targets) ? : array();
        return $this->createSerializer()->unserialize($results, $this->serializeFields);
    }

    private function _createSearchQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions);

        if (isset($conditions['targetPrefix'])) {
            $conditions['targetLike'] = "{$conditions['targetPrefix']}%";
            unset($conditions['target']);
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'questions')
            ->andWhere('target = :target')
            ->andWhere('target LIKE :targetLike');

        return $builder;
    }

}