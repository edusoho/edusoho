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

    public function searchTestpapers($conditions, $sort, $start, $limit)
    {

    }

    public function searchTestpapersCount($conditions)
    {

    }

    public function addTestpaper($fields)
    {

    }

    public function updateTestpaper($id, $fields)
    {

    }
}