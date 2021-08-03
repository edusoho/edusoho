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

    const allowSettingNames = [];

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function set($settingName, $content)
    {
        if (!in_array($settingName, self::allowSettingNames, true)) {
            throw CommonException::ERROR_PARAMETER();
        }
        $this->getSettingService()->set($settingName, $content);
    }

    public function get($settingName, $default)
    {
        if (!in_array($settingName, self::allowSettingNames, true)) {
            throw CommonException::ERROR_PARAMETER();
        }

        return $this->getSettingService()->get($settingName, $default);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
