<?php

namespace Biz\CloudPlatform;

class UpgradeAgreement
{
    public static function getAgreement($version)
    {
        $agreements = [
            '21.1.1' => [
                'trans' => 'admin.app_upgrades.agreement.content.21.1.2',
            ],
        ];

        return empty($agreements[$version]) ? [] : $agreements[$version];
    }
}
