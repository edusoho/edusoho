<?php

namespace AppBundle\Util;

use Topxia\Service\Common\ServiceKernel;

class CdnUrl
{
    public function get($package = 'default')
    {
        try {
            $cdn = ServiceKernel::instance()->getBiz()->service('System:SettingService')->get('cdn', array());
            $cdnUrls = (empty($cdn['enabled'])) ? array() : array(
                'defaultUrl' => $this->url($cdn['defaultUrl']),
                'userUrl' => $this->url($cdn['userUrl']),
                'contentUrl' => $this->url($cdn['contentUrl']),
            );

            if ($cdnUrls) {
                return $cdnUrls[$package.'Url'] ?: $cdnUrls['defaultUrl'];
            }

            return '';
        } catch (\Exception $e) {
            return '';
        }
    }

    private function url($url)
    {
        if (0 === strpos($url, 'https://')) {
            $url = substr($url, 8);
        } elseif (0 === strpos($url, 'http://')) {
            $url = substr($url, 7);
        }

        return rtrim($url, " \/");
    }
}
