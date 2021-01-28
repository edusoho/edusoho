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
     * @Log(module="announcement",action="create")
     */
    public function createAnnouncement($fields);

    public function updateAnnouncement($id, $fields);

    /**
     * @param $id
     *
     * @return mixed
     * @Log(module="announcement",action="delete")
     */
    public function deleteAnnouncement($id);
}
