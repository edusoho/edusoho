<?php

namespace Biz\CloudPlatform;

use Topxia\Service\Common\ServiceKernel;

class UpgradeAgreement
{
    const VERSION = '21.1.1';

    public function getAgreement($version)
    {
        $agreements = [
            self::VERSION => [
                'trans' => 'admin.app_upgrades.agreement.content.21.1.2',
            ],
        ];

        return empty($agreements[$version]) || !$this->isAgreementShow($version) ? [] : $agreements[$version];
    }

    protected function isAgreementShow($version)
    {
        if (self::VERSION == $version) {
            $backstage = $this->getSettingService()->get('backstage', ['is_v2' => 1]);
            if (empty($backstage['allow_show_switch_btn']) && !empty($backstage['is_v2'])) {
                return false;
            }

            return true;
        }

        return false;
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->getBiz()->service('System:SettingService');
    }
}
