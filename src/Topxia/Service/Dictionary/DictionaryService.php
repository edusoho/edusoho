<?php

namespace Topxia\Service\Dictionary;

interface DictionaryService
{
	public function getDictionary($id);

	public function deleteDictionary($id);

	public function updateDictionary($id, $fields);

	public function addDictionary($fields);

	public function findAllDictionariesOrderByWeight();

	public function findDictionaryByName($name);

}