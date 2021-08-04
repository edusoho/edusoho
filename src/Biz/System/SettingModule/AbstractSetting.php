<?php

namespace Biz\System\SettingModule;

use Biz\Common\CommonException;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Context\Biz;

abstract class AbstractSetting
{
    /**
     * @var Biz
     */
    protected $biz;

    protected $allowSettingNames = [];

    public function getAllowSettingNames()
    {
        return $this->allowSettingNames;
    }

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function set($settingName, $content)
    {
        if (!in_array($settingName, $this->getAllowSettingNames(), true)) {
            throw CommonException::ERROR_PARAMETER();
        }
        $this->getSettingService()->set($settingName, $content);
    }

    public function get($settingName, $default = [])
    {
        if (!in_array($settingName, $this->getAllowSettingNames(), true)) {
            throw CommonException::ERROR_PARAMETER();
        }
        $setting = $this->getSettingService()->get($settingName);

        return array_merge($default, $setting);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
