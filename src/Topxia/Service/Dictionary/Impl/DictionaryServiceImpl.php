<?php

namespace Topxia\Service\Dictionary\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Dictionary\DictionaryService;
use Topxia\Common\ArrayToolkit;

class DictionaryServiceImpl extends BaseService implements DictionaryService
{
	public function getDictionary($id)
	{
		return $this->getDictionaryDao()->getDictionary($id);
	}

	public function deleteDictionary($id)
	{
		$this->getDictionaryDao()->deleteDictionary($id);
	}

	public function updateDictionary($id, $fields)
	{
		$fields = ArrayToolkit::parts($fields, array('type', 'type', 'name', 'weight'));
        return $this->getDictionaryDao()->updateDictionary($id, $fields);
	}

	public function addDictionary($fields)
	{
		return $this->getDictionaryDao()->addDictionary($fields);
	}

	public function findAllDictionariesOrderByWeight()
	{
		return $this->getDictionaryDao()->findAllDictionariesOrderByWeight();
	}
	
	public function findDictionaryByName($name)
	{
		return $this->getDictionaryDao()->findDictionaryByName($name);
	}

	protected function getDictionaryDao()
	{
		return $this->createDao('Dictionary.DictionaryDao');
	}
}