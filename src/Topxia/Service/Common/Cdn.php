<?php
namespace Topxia\Service\Common;

use Topxia\Service\Common\BaseService;

class Cdn extends BaseService
{
    public function getUrl($package = 'default')
    {
        $cdn    = $this->createService('System.SettingService')->get('cdn', array());
        $cdnUrls = (empty($cdn['enabled'])) ? array() : array('defaultUrl' => rtrim($cdn['defaultUrl'], " \/"), 'userUrl' => rtrim($cdn['userUrl'], " \/"), 'contentUrl' => rtrim($cdn['contentUrl'], " \/"));

        if ($cdnUrls) {
            return $cdnUrls[$package.'Url'] ? : $cdnUrls['defaultUrl'];
        }

        return '';
    }
}
