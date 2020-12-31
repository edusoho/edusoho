<?php

namespace ApiBundle\Security\Firewall;

use ApiBundle\ApiSecurityException;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\DeviceToolkit;
use Biz\System\Service\SettingService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class ApiSecurityAuthenticationListener implements ListenerInterface
{
    const CLIENT_IOS = 'ios';

    const CLIENT_ANDROID = 'android';

    const CLIENT_MINIPROGRAM = 'miniprogram';

    const CLIENT_OTHER = 'other';

    const API_SECURITY_CLOSE = 'close';

    const API_SECURITY_OPEN = 'open';

    const API_SECURITY_OPTIONAL = 'optional';

    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function handle(Request $request)
    {
        if (0 === stripos($request->getPathInfo(), '/api/security_sign')) {
            return;
        }

        $apiSecuritySetting = $this->getSettingService()->get('api_security', []);
        if (!isset($apiSecuritySetting['level']) || self::API_SECURITY_CLOSE === $apiSecuritySetting['level']) {
            return;
        }

        if (isset($apiSecuritySetting['client'])
            && is_array($apiSecuritySetting['client'])
            && in_array($this->getClient($request), $apiSecuritySetting['client'], true)) {
            if (self::API_SECURITY_OPTIONAL === $apiSecuritySetting['level'] && empty($request->query->get('api_signature'))) {
                return;
            }

            if (self::API_SECURITY_OPEN === $apiSecuritySetting['level'] && empty($request->query->get('api_signature'))) {
                throw ApiSecurityException::SIGN_ERROR();
            }

            $this->checkSignature($request);
        }
    }

    private function checkSignature(Request $request)
    {
        $params = $request->query->all();
        $sign = $params['api_signature'];
        unset($sign['api_signature']);
        $content = trim($request->getContent());
        if (!empty($content)) {
            $params['bodyContent'] = $request->getContent();
        }
        $params = ArrayToolkit::flatten($params);
        ksort($res);
        $data = [];
        foreach ($params as $key => $value) {
            $data[] = $key.'='.$value;
        }
        $data = implode('&', $data);
        if (md5($data.'test123456') !== $sign) {
            throw ApiSecurityException::SIGN_ERROR();
        }
    }

    private function getClient(Request $request)
    {
        $client = DeviceToolkit::getClient($request->headers->get('user-agent'));
        switch ($client) {
            case 'ios':
                $client = self::CLIENT_IOS;
                break;
            case 'android':
                $client = self::CLIENT_ANDROID;
                break;
            case 'miniprogram':
                $client = self::CLIENT_MINIPROGRAM;
                break;
            default:
                $client = self::CLIENT_OTHER;
                break;
        }

        return $client;
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    private function createService($service)
    {
        return $this->container->get('biz')->service($service);
    }
}
