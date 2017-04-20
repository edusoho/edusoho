<?php

namespace Topxia\Api\Convert;
use Topxia\Service\Common\ServiceKernel;

class AnnouncementConvert implements Convert
{
    //根据id等参数获取完整数据
    public function convert($id)
    {
        $announcement = ServiceKernel::instance()->createService('Announcement:AnnouncementService')->getAnnouncement($id);
        if (empty($announcement)) {
            throw new \Exception('announcement not found');
        }
        return $announcement;
    }

}

