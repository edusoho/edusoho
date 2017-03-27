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

	public function get($id)
    {
        $_this = $this;
        return $this->fetchCached("id:{$id}", $id, function ($id) use ($_this) {
            $sql = "SELECT * FROM {$_this->getTable()} WHERE id = ? LIMIT 1";
            return $_this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        });
    }

	public function create($dictionaries)
    {
        $affected = $this->getConnection()->insert($this->table, $dictionaries);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert course error.');
        }

        $course = $this->get($this->getConnection()->lastInsertId());
        $this->clearCached();
        return $course;
    }
}