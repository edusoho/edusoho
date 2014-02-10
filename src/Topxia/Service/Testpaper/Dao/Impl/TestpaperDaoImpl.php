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