<?php
namespace Topxia\Service\Dictionary\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Dictionary\Dao\DictionaryDao;

class DictionaryDaoImpl extends BaseDao implements DictionaryDao
{
	protected $table = 'dictionary';

	public function getDictionary($id)
	{
		$sql = "SELECT * FROM {$this->table} where id=? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
	}

	public function deleteDictionary($id)
	{
		return $this->getConnection()->delete($this->table, array('id' => $id));
	}

	public function updateDictionary($id, $fields)
	{
		$fields['updateTime'] = time();
		$this->getConnection()->update($this->table, $fields, array('id' => $id));

        return $this->getDictionary($id);
	}

	public function addDictionary($fields)
	{
		$affected = $this->getConnection()->insert($this->table, $fields);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert Dictionary error.');
        }

        return $this->getDictionary($this->getConnection()->lastInsertId());
	}

	public function findAllDictionariesOrderByWeight()
	{
		$sql = "SELECT * FROM {$this->table} ORDER BY weight DESC";
        return $this->getConnection()->fetchAll($sql, array());
	}

	public function findDictionaryByName($name)
    {
        $sql = "SELECT * FROM {$this->table} where name = ?";
        $dictionary = $this->getConnection()->fetchAll($sql, array($name));
        return $dictionary;
    }

}