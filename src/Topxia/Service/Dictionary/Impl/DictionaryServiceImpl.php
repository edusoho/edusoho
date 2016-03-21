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

	protected function getDictionaryDao()
	{
		return $this->createDao('Dictionary.DictionaryDao');
	}
}