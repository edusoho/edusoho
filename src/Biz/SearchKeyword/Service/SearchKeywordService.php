<?php

namespace Biz\SearchKeyword\Service;

interface SearchKeywordService
{
    public function createSearchKeyword($keyword);

    public function updateSearchKeyword($id, $keyword);

    public function getSearchKeyword($id);

    public function getSearchKeywordByNameAndType($name, $type);

    public function searchSearchKeywords($conditions, $orderBy, $start, $limit);

    public function countSearchKeywords($conditions);

    public function deleteSearchKeyword($id);

    public function addSearchKeywordTimes($id);
}
