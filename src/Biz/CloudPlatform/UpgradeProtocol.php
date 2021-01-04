<?php

namespace Biz\CloudPlatform;

class UpgradeProtocol
{
    public static function getProtocol($version)
    {
        $protocols = [
            '21.1.1' => [
                'trans' => 'admin.app_upgrades.protocol.content.21.1.2',
            ]
        ];

        return empty($protocols[$version]) ? [] : $protocols[$version];
    }
}
