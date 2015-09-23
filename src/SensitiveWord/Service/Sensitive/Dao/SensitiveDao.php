<?php

namespace SensitiveWord\Service\Sensitive\Dao;

interface SensitiveDao
{

    public function getKeywordByName($name);

    public function findAllKeywords();

    public function addKeyword(array $fields);

    public function deleteKeyword($id);

    public function searchkeywordsCount();

    public function searchKeywords($start, $limit);

    public function waveBannedNum($id, $diff);



}