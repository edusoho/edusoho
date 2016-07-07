<?php
namespace Topxia\Service\Dictionary\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Dictionary\DictionaryService;
use Topxia\Common\ArrayToolkit;

class DictionaryServiceImpl extends BaseService implements DictionaryService
{
	public function findAllDictionaries()
	{
		return $this->getDictionaryDao()->findAllDictionaries();
	}

	public function getDictionaryItem($id)
	{
		return $this->getDictionaryItemDao()->getDictionaryItem($id);
	}

	public function deleteDictionaryItem($id)
	{
		$this->getDictionaryItemDao()->deleteDictionaryItem($id);
	}

	public function updateDictionaryItem($id, $fields)
	{
		$fields = ArrayToolkit::parts($fields, array('type', 'type', 'name', 'weight'));
        return $this->getDictionaryItemDao()->updateDictionaryItem($id, $fields);
	}

	public function addDictionaryItem($fields)
	{
		return $this->getDictionaryItemDao()->addDictionaryItem($fields);
	}

	public function findAllDictionaryItemsOrderByWeight()
	{
		return $this->getDictionaryItemDao()->findAllDictionaryItemsOrderByWeight();
	}
	
	public function findDictionaryItemByName($name)
	{
		return $this->getDictionaryItemDao()->findDictionaryItemByName($name);
	}

	public function findDictionaryItemByType($type)
	{
		return $this->getDictionaryItemDao()->findDictionaryItemByType($type);
	}

	protected function getDictionaryItemDao()
	{
		return $this->createDao('Dictionary.DictionaryItemDao');
	}
	
	protected function getDictionaryDao()
	{
		return $this->createDao('Dictionary.DictionaryDao');
	}
}