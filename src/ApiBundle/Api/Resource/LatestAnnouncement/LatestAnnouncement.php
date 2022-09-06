<?php

namespace ApiBundle\Api\Resource\LatestAnnouncement;

use ApiBundle\Api\Resource\AbstractResource;
use Biz\Announcement\Service\AnnouncementService;

class LatestAnnouncement extends AbstractResource
{
    public function get(){
        return $this->getAnnouncementService()->searchAnnouncements(['startTime' => time(), 'endTime' => time(), 'targetType' => 'global'], ['startTime' => 'DESC'], 0, 1);
    }

    /**
     * @return AnnouncementService
     */
    protected function getAnnouncementService()
    {
        return $this->service('Announcement:AnnouncementService');
    }
}