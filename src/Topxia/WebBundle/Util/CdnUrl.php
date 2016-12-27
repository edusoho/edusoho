<?php
namespace Topxia\WebBundle\Util;

use Topxia\Service\Common\ServiceKernel;

class CdnUrl
{
    public function get($package = 'default')
    {
        try{
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
        } catch (\Exception $e) {
            // TODO 删除缓存后的第一次访问时，由于container还未初始化，会报错
            return '';
        }
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
