<?php

namespace Biz\Announcement\Service;

use Biz\System\Annotation\Log;

interface AnnouncementService
{
    public function searchAnnouncements($conditions, $sort, $start, $limit);

    public function countAnnouncements($conditions);

    public function getAnnouncement($id);

    /**
     * @param $fields
     *
     * @return mixed
     * @Log(level="info",module="announcement",action="create",message="创建公告",targetType="announcement",param="result")
     */
    public function createAnnouncement($fields);

    public function updateAnnouncement($id, $fields);

    public function deleteAnnouncement($id);
}
