<?php
namespace Topxia\WebBundle\Util;

use Topxia\Service\Common\ServiceKernel;

class CdnUrl
{
   	public function get($package = 'default')
    {
        //@fixme 为能跑单元测试，只能这么干了，请在8.0发布之前修复这个问题。
        try {
            $cdn     = ServiceKernel::instance()->createService('System.SettingService')->get('cdn', array());
        } catch (\Exception $e) {
            $cdn = array();
        }

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
