<?php
namespace Topxia\WebBundle\Util;

use Topxia\Service\Common\ServiceKernel;

class CdnUrl
{
    public function get($package = 'default')
    {
        $cdn     = ServiceKernel::instance()->createService('System.SettingService')->get('cdn', array());
        $cdnUrls = (empty($cdn['enabled'])) ? array() : array('defaultUrl' => rtrim($cdn['defaultUrl'], " \/"), 'userUrl' => rtrim($cdn['userUrl'], " \/"), 'contentUrl' => rtrim($cdn['contentUrl'], " \/"));

        if ($cdnUrls) {
            return $cdnUrls[$package.'Url'] ?: $cdnUrls['defaultUrl'];
        }

        return '';
    }
}
