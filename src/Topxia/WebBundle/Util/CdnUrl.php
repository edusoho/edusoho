<?php
namespace Topxia\WebBundle\Util;

use Topxia\Service\Common\ServiceKernel;

class CdnUrl
{
   	public function get($package = 'default')
    {
        $cdn     = ServiceKernel::instance()->createService('System.SettingService')->get('cdn', array());
        $cdnUrls = (empty($cdn['enabled'])) ? array() : array(
        	'defaultUrl' => $this->url($cdn['defaultUrl']), 
        	'userUrl' => $this->url($cdn['userUrl']), 
        	'contentUrl' => $this->url($cdn['contentUrl'])
        );

        if ($cdnUrls) {
            return $cdnUrls[$package.'Url'] ?: $cdnUrls['defaultUrl'];
        }

        return '';
    }

    private function url($url)
    {
        if(strpos($url, 'https://') === 0) {
            $url = substr($url, 8);
        } elseif(strpos($url, 'http://') === 0) {
            $url = substr($url, 7);
        }
        $url = '//'.$url;
        return rtrim($url, " \/");
    }
}
