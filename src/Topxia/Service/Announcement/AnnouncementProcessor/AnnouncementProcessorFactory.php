<?php
namespace Topxia\Service\Announcement\AnnouncementProcessor;

use Topxia\Service\Announcement\AnnouncementProcessor\AnnouncementProcessor;

class AnnouncementProcessorFactory
{
    public static function create($target)
    {
        if (empty($target) || !in_array($target, array('course', 'classroom'))) {
            throw new \Exception(self::getKernel()->trans('公告类型不存在'));
        }

        $class = __NAMESPACE__.'\\'.ucfirst($target).'AnnouncementProcessor';

        return new $class();
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }
}
