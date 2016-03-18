<?php

namespace Topxia\Service\Dictionary\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Dictionary\DictionaryItemService;
use Topxia\Common\ArrayToolkit;

class DictionaryItemServiceImpl extends BaseService implements DictionaryItemService
{
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

	protected function getDictionaryItemDao()
	{
		return $this->createDao('Dictionary.DictionaryItemDao');
	}
}