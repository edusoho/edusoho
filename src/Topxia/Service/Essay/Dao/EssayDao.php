<?php

namespace Topxia\Service\Essay\Dao;

interface EssayDao
{
    public function getEssay($id);

    public function searchEssays(array $conditions, array $oderBy, $start, $limit);

    public function searchEssaysCount(array $conditions);

    public function addEssay(array $essay);

    public function updateEssay($id, array $essay);
    
    public function deleteEssay($id);
}