<?php
namespace Topxia\Service\Common;

use Topxia\Service\Common\BaseService;

class Cdn extends BaseService
{
    public function getCdnUrl($package = 'default')
    {
        $cdn    = $this->createService('System.SettingService')->get('cdn', array());
        $cdnUrls = (empty($cdn['enabled'])) ? array() : array('defaultUrl' => rtrim($cdn['defaultUrl'], " \/"), 'userUrl' => rtrim($cdn['userUrl'], " \/"), 'contentUrl' => rtrim($cdn['contentUrl'], " \/"), 'frontUrl' => rtrim($cdn['frontUrl'], " \/"));

        if ($cdnUrls) {
            return $cdnUrls[$package.'Url'] ? : $cdnUrls['defaultUrl'];
        }

        return '';
    }
}
