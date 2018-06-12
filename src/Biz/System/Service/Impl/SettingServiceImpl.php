<?php

namespace Biz\System\Service\Impl;

use Biz\BaseService;
use Biz\System\Service\SettingService;

class SettingServiceImpl extends BaseService implements SettingService
{
    const CACHE_NAME = 'settings';
    const NAME_SPACE_DEFAULT = 'default';

    private $cached;

    public function set($name, $value)
    {
        $this->getSettingDao()->deleteByName($name);
        $setting = array(
            'name' => $name,
            'value' => serialize($value),
        );
        $this->getSettingDao()->create($setting);
        $this->clearCache();
    }

    public function get($name, $default = array())
    {
        if (is_null($this->cached)) {
            $this->cached = $this->getCacheService()->get(self::CACHE_NAME);

            if (is_null($this->cached)) {
                $settings = $this->getSettingDao()->findAll();
                foreach ($settings as $setting) {
                    $this->cached[$setting['namespace'].'-'.$setting['name']] = $setting['value'];
                }
                $this->getCacheService()->set(self::CACHE_NAME, $this->cached);
            }
        }

        $namespace = $this->getNameSpace();
        $defaultSet = isset($this->cached[self::NAME_SPACE_DEFAULT.'-'.$name]) ? unserialize($this->cached[self::NAME_SPACE_DEFAULT.'-'.$name]) : $default;
        $orgSet = isset($this->cached[$namespace.'-'.$name]) ? unserialize($this->cached[$namespace.'-'.$name]) : $default;

        return empty($orgSet) ? $defaultSet : $this->mergeSetting($defaultSet, $orgSet);
    }

    private function mergeSetting($defaultSet, $orgSet)
    {
        if (is_array($orgSet)) {
            return array_merge($defaultSet, $orgSet);
        } else {
            return $orgSet;
        }
    }

    public function delete($name)
    {
        $this->getSettingDao()->deleteByName($name);
        $this->clearCache();
    }

    public function setByNamespace($namespace, $name, $value)
    {
        $this->getSettingDao()->deleteByNamespaceAndName($namespace, $name);
        $setting = array(
            'namespace' => $namespace,
            'name' => $name,
            'value' => serialize($value),
        );
        $this->getSettingDao()->create($setting);
        $this->clearCache();
    }

    public function deleteByNamespaceAndName($namespace, $name)
    {
        $this->getSettingDao()->deleteByNamespaceAndName($namespace, $name);
        $this->clearCache();
    }

    public function isReservationOpen()
    {
        $setting = $this->get('plugin_reservation', array());

        return !empty($setting['reservation_enabled']) && 1 == $setting['reservation_enabled'];
    }

    protected function clearCache()
    {
        $this->getCacheService()->clear(self::CACHE_NAME);
        $this->cached = null;
    }

    protected function getCacheService()
    {
        return $this->biz->service('System:CacheService');
    }

    protected function getSettingDao()
    {
        return $this->createDao('System:SettingDao');
    }

    protected function getNameSpace()
    {
        try {
            $user = $this->getCurrentUser()->toArray();
            if (empty($user['selectedOrgId']) || 1 === $user['selectedOrgId']) {
                return 'default';
            }

            return 'org-'.$user['selectedOrgId'];
        } catch (\RuntimeException $e) {
            return 'default';
        }
    }
}
