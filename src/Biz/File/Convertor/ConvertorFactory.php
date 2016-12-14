<?php
namespace Biz\File\Convertor;


use Topxia\Service\Util\CloudClient;

class ConvertorFactory
{

    /**
     * @param $name                        string      转码器名
     * @param $cloudClient                 CloudClient 云平台Client
     * @param $cloudConvertorDefaultConfig array       转码配置
     *
     * @return mixed
     * @throws \Exception
     */
    public static function create($name, CloudClient $cloudClient, array $cloudConvertorDefaultConfig)
    {
        if (empty($name) || !in_array($name, array('HLSVideo', 'HLSEncryptedVideo', 'Audio', 'Document', 'Ppt'))) {
            throw new \Exception('转码类型不存在');
        }

        $class = __NAMESPACE__ . '\\' . ucfirst($name) . 'Convertor';

        return new $class($cloudClient, $cloudConvertorDefaultConfig);
    }
}


