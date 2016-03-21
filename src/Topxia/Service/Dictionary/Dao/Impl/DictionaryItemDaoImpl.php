<?php
namespace Topxia\Service\Dictionary\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Dictionary\Dao\DictionaryItemDao;

class DictionaryItemDaoImpl extends BaseDao implements DictionaryItemDao
{
	protected $table = 'dictionary_item';

	public function getDictionaryItem($id)
	{
		$sql = "SELECT * FROM {$this->table} where id=? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
	}

	public function deleteDictionaryItem($id)
	{
		return $this->getConnection()->delete($this->table, array('id' => $id));
	}

	public function updateDictionaryItem($id, $fields)
	{
		$fields['updateTime'] = time();
		$this->getConnection()->update($this->table, $fields, array('id' => $id));

        return $this->getDictionaryItem($id);
	}

	public function addDictionaryItem($fields)
	{
		$affected = $this->getConnection()->insert($this->table, $fields);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert DictionaryItem error.');
        }

        return $this->getDictionaryItem($this->getConnection()->lastInsertId());
	}

	public function findAllDictionaryItemsOrderByWeight()
	{
		$sql = "SELECT * FROM {$this->table} ORDER BY weight DESC";
        return $this->getConnection()->fetchAll($sql, array());
	}

	public function findDictionaryItemByName($name)
    {
        $sql = "SELECT * FROM {$this->table} where name = ?";
        $dictionary = $this->getConnection()->fetchAll($sql, array($name));
        return $dictionary;
    }

    public function findDictionaryItemByType($type)
    {
    	$sql = "SELECT * FROM {$this->table} where type = ?";
        $dictionary = $this->getConnection()->fetchAll($sql, array($type));
        return $dictionary;
    }

}