<?php
namespace Topxia\WebBundle\Util;

use Topxia\Service\Common\BaseService;

class CdnUrl extends BaseService
{
    public function get($package = 'default')
    {
        $cdn    = $this->createService('System.SettingService')->get('cdn', array());
        $cdnUrls = (empty($cdn['enabled'])) ? array() : array('defaultUrl' => rtrim($cdn['defaultUrl'], " \/"), 'userUrl' => rtrim($cdn['userUrl'], " \/"), 'contentUrl' => rtrim($cdn['contentUrl'], " \/"));

        if ($cdnUrls) {
            return $cdnUrls[$package.'Url'] ? : $cdnUrls['defaultUrl'];
        }

        return '';
    }
}
