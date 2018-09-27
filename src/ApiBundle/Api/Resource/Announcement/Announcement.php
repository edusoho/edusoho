<?php

namespace ApiBundle\Api\Resource\Announcement;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Announcement\Service\AnnouncementService;
use Biz\Announcement\AnnouncementException;

class Announcement extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $startTime = $request->query->get('startTime', 0);
        $targetType = $request->query->get('targetType', 'global');
        $conditions = array(
            'targetType' => $targetType,
            'startTime_GT' => $startTime,
        );

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $announcements = $this->getAnnouncementService()->searchAnnouncements(
            $conditions,
            array('createdTime' => 'DESC'),
            $offset,
            $limit
        );

        $total = $this->getAnnouncementService()->countAnnouncements($conditions);

        return $this->makePagingObject($announcements, $total, $offset, $limit);
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $id)
    {
        $announcement = $this->getAnnouncementService()->getAnnouncement($id);
        if (!$announcement) {
            throw AnnouncementException::ANNOUNCEMENT_NOT_FOUND();
        }

        return $announcement;
    }

    /**
     * @return AnnouncementService
     */
    protected function getAnnouncementService()
    {
        return $this->service('Announcement:AnnouncementService');
    }
}
