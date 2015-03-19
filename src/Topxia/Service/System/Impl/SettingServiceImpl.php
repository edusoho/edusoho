<?php
namespace Topxia\Service\System\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\System\SettingService;

class SettingServiceImpl extends BaseService implements SettingService
{
    const CACHE_NAME = 'settings';

    private $cached;

    public function set($name, $value)
    {
        $this->getSettingDao()->deleteSettingByName($name);
        $setting = array(
            'name'  => $name,
            'value' => serialize($value)
        );
        $this->getSettingDao()->addSetting($setting);
        $this->clearCache();
    }

    public function get($name, $default = NULL)
    {
        if (is_null($this->cached)) {
            $this->cached = $this->getCacheService()->get(self::CACHE_NAME);
            if (is_null($this->cached)) {
                $settings = $this->getSettingDao()->findAllSettings();
                foreach ($settings as $setting) {
                    $this->cached[$setting['name']] = $setting['value'];
                }
                $this->getCacheService()->set(self::CACHE_NAME, $this->cached);
            }
        }

        return isset($this->cached[$name]) ? unserialize($this->cached[$name]) : $default;
    }

    public function delete($name)
    {
        $this->getSettingDao()->deleteSettingByName($name);
        $this->clearCache();
    }

    

    protected function clearCache()
    {
        $this->getCacheService()->clear(self::CACHE_NAME);
        $this->cached = null;
    }

    protected function getCacheService()
    {
        return $this->createService('System.CacheService');
    }

    protected function getSettingDao ()
    {
        return $this->createDao('System.SettingDao');
    }

}