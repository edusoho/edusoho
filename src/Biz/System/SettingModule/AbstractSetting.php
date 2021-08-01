<?php

namespace Biz\System\SettingModule;

use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Context\Biz;

abstract class AbstractSetting
{
    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
