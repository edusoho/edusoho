<?php

namespace Topxia\Service\Dictionary\Dao;

interface DictionaryDao
{
	public function getDictionary($id);

	public function deleteDictionary($id);

	public function updateDictionary($id, $fields);

	public function addDictionary($fields);

	public function findAllDictionariesOrderByWeight();

	public function findDictionaryByName($name);

}