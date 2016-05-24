<?php

namespace Topxia\Service\Dictionary;

interface DictionaryService
{
	public function findAllDictionaries();

	public function getDictionaryItem($id);

	public function deleteDictionaryItem($id);

	public function updateDictionaryItem($id, $fields);

	public function addDictionaryItem($fields);

	public function findAllDictionaryItemsOrderByWeight();

	public function findDictionaryItemByName($name);

	public function findDictionaryItemByType($type);

}