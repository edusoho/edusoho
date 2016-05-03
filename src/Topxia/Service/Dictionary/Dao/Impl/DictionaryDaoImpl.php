<?php
namespace Topxia\Service\Dictionary\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Dictionary\Dao\DictionaryDao;

class DictionaryDaoImpl extends BaseDao implements DictionaryDao
{
	protected $table = 'dictionary';

	public function findAllDictionaries()
	{
		$sql = "SELECT * FROM {$this->table} ";
        return $this->getConnection()->fetchAll($sql, array());
	}
}