<?php
namespace Topxia\Service\System\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\System\SettingService;

class SettingServiceImpl extends BaseService implements SettingService
{
    const CACHE_NAME = 'settings';
    const NAME_SPACE = 'default';

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

// TODO 获得当前组织结构ID
    public function get($name, $default = null)
    {
        $user = $this->getCurrentUser()->toArray();

        if (is_null($this->cached)) {
            $this->cached = $this->getCacheService()->get(self::CACHE_NAME);

            if (is_null($this->cached)) {
                $settings = $this->getSettingDao()->findAllSettings();
                foreach ($settings as $setting) {
                    $this->cached[$setting['namespace'].'-'.$setting['name']] = $setting['value'];
                }
                $this->getCacheService()->set(self::CACHE_NAME, $this->cached);
            }
        }
        $defaultSet= isset($this->cached['default-'.$name]) ? unserialize($this->cached['default-'.$name]) : $default ;
        $orgSet = isset($this->cached['org-20-'.$name]) ? unserialize($this->cached['org-20-'.$name]) : array();
        return empty( $defaultSet ) ? $defaultSet : array_merge( $defaultSet , $orgSet );
    }

    public function delete($name)
    {
        $this->getSettingDao()->deleteSettingByName($name);
        $this->clearCache();
    }

    public function setByNamespace($namespace,$name,$value)
    {
        $this->getSettingDao()->deleteByNamespaceAndName($namespace,$name);
        $setting = array(
            'namespace' => $namespace,
            'name'  => $name,
            'value' => serialize($value)
        );
        $this->getSettingDao()->addSetting($setting);
        $this->clearCache();

    }

    public function deleteByNamespaceAndName($namespace,$name)
    {
        $this->getSettingDao()->deleteByNamespaceAndName($namespace,$name);
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

    protected function getSettingDao()
    {
        return $this->createDao('System.SettingDao');
    }

    protected function getNameSpace(){
        return self::NAME_SPACE;
    } 
}
