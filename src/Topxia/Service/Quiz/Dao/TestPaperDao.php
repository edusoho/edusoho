<?php

namespace Topxia\Service\Quiz\Dao;

interface TestPaperDao
{
    public function getTestPaper($id);
    
    public function addTestPaper($testPaper);

    public function updateTestPaper($id, $fields);

    public function deleteTestPaper($id);

    public function deleteTestPapersByParentId($id);

    public function findTestPaperByIds(array $ids);

    public function deleteTestPaperByIds(array $ids);

    public function searchTestPaper($conditions, $orderBy, $start, $limit);

    public function searchTestPaperCount($conditions);
}