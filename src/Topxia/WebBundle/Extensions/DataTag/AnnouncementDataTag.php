<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Biz\Announcement\Service\AnnouncementService;

/**
 * @todo  
 */
class AnnouncementDataTag extends BaseDataTag implements DataTag  
{

    /**
     * 获取公告列表
     *
     * 可传入的参数：
     *   count    必需 取值不超过10
     * 
     * @param  array $arguments 参数
     * @return array 公告列表
     */
    public function getData(array $arguments)
    {   
        $this->checkCount($arguments);

        $currentTime = time();

        $conditions  = $this->fillOrgCode(array('targetType'=>'global', 'startTime'=>$currentTime, 'endTime'=>$currentTime));

        $announcement = $this->getAnnouncementService()->searchAnnouncements($conditions,array('createdTime' => 'DESC'), 0, $arguments['count']);
        
        return $announcement;
    }

    /**
     * @return AnnouncementService
     */
    protected function getAnnouncementService()
    {
        return $this->getServiceKernel()->getBiz()->service('Announcement:AnnouncementService');
    }

    protected function checkCount(array $arguments)
    {
        if (empty($arguments['count'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('count参数缺失'));
        }
        if ($arguments['count'] > 100) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('count参数超出最大取值范围'));
        }
    }
}
