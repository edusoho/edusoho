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
     * @Log(level="info",module="announcement",action="create",message="创建公告",targetType="announcement")
     */
    public function createAnnouncement($fields);

    public function updateAnnouncement($id, $fields);

    /**
     * @param $id
     *
     * @return mixed
     * @Log(level="info",module="announcement",action="delete",message="删除公告",targetType="announcement",format="{'before':{ 'className':'Announcement:AnnouncementService','funcName':'getAnnouncement','param':['id']}}")
     */
    public function deleteAnnouncement($id);
}
