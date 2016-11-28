<?php

namespace Topxia\Service\Dictionary\Dao;

interface DictionaryDao
{
	public function findAllDictionaries();

    public function create($dictionary);

    public function get($id);
}