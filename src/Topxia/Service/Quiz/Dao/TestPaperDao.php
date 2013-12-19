<?php

namespace Topxia\Service\Quiz\Dao;

interface TestPaperDao
{
    public function getPaper($id);
    
    public function addPaper($questions);

    public function updatePaper($id, $fields);

    public function deletePaper($id);

    public function deletePapersByParentId($id);

    public function findPaperByIds(array $ids);

    public function deletePaperByIds(array $ids);

    public function searchPaperCount($conditions);

    public function searchPaper($conditions, $orderBy, $start, $limit);
}