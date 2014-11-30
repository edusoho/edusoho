<?php
namespace Topxia\Service\Essay;

interface EssayService
{
    public function getEssay($id);

    public function searchEssays(array $conditions, array $sort, $start, $limit);

    public function searchEssaysCount(array $conditions);

    public function createEssay(array $essay);

    public function updateEssay($id,$essay);

    public function deleteEssay($id);

    public function deleteEssaysByIds($ids);
 
    public function publishEssay($id);

    public function unpublishEssay($id);
}