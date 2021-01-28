<?php

namespace AppBundle\Extensions\DataTag;

use Biz\Announcement\Service\AnnouncementService;

/**
 * @todo
 */
class AnnouncementsDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取公告列表.
     *
     * 可传入的参数：
     *   count      必需 取值不超过10
     *   targetId   目标id
     *   targetType 目标类型
     *
     * @param array $arguments 参数
     *
     * @return array 公告列表
     */
    public function getData(array $arguments)
    {
        $this->checkCount($arguments);

        $currentTime = time();
        // $currentTime = $currentTime - $currentTime%900;

        $conditions = array(
            'targetType' => $arguments['targetType'],
            'targetId' => $arguments['targetId'],
            'endTime' => $currentTime,
        );

        $announcements = $this->getAnnouncementService()->searchAnnouncements($conditions, array('createdTime' => 'DESC'), 0, $arguments['count']);

        return $announcements;
    }

    /**
     * @return AnnouncementService
     */
    protected function getAnnouncementService()
    {
        return $this->getServiceKernel()->createService('Announcement:AnnouncementService');
    }

    protected function checkCount(array $arguments)
    {
        if (empty($arguments['count'])) {
            throw new \InvalidArgumentException('count参数缺失');
        }

        if ($arguments['count'] > 100) {
            throw new \InvalidArgumentException('count参数超出最大取值范围');
        }
    }
}
