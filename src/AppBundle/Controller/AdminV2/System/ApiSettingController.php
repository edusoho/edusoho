<?php

namespace AppBundle\Controller\AdminV2\System;

use Biz\System\Service\SettingService;
use AppBundle\Controller\AdminV2\BaseController;
use Symfony\Component\HttpFoundation\Request;

class ApiSettingController extends BaseController
{
    public function apiKeyAction(Request $request)
    {
        $settings = $this->getSettingService()->get('api', []);

        if (empty($settings['api_app_id']) || empty($settings['api_app_secret_key'])) {
            $setting['api_app_id'] = $this->randomkeys('16');
            $setting['api_app_secret_key'] = $this->randomkeys('32');
            $settings = $this->getSettingService()->set('api', $setting);
        }

        if ('POST' === $request->getMethod()) {
            $data = $request->request->all();
            $purifiedWhiteIps = isset($data['whiteListIps']) && $data['whiteListIps'] ? $data['whiteListIps'] : null;
            $purifiedWhiteIps = trim(preg_replace('/\s+/', ' ', $purifiedWhiteIps));

            if (empty($purifiedWhiteIps)) {
                $settings['ip_white_list'] = '';
            } else {
                $settings['ip_white_list'] = array_filter(explode(' ', $purifiedWhiteIps));
            }
            $settings['external_switch'] = isset($data['external_switch']) ? $data['external_switch'] : 0;
            $this->getSettingService()->set('api', $settings);
            $this->setFlashMessage('success', 'site.save.success');
        }

        $settings = $this->getSettingService()->get('api', []);
        $whiteListIps = [];

        if (!empty($settings['ip_white_list'])) {
            $whiteListIps['ips'] = $settings['ip_white_list'];
            $default['ips'] = join("\n", $whiteListIps['ips']);
            $whiteListIps = array_merge($whiteListIps, $default);
        }

        return $this->render('admin-v2/system/api-setting/api-setting.html.twig', [
            'info' => $settings,
            'whiteListIps' => $whiteListIps,
        ]);
    }

    protected function randomkeys($length)
    {
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
        $str = '';
        for ($i = 0; $i < $length; ++$i) {
            $str .= substr($pattern, mt_rand(0, strlen($pattern) - 1), 1);
        }

        return $str;
    }

    public function apiKeyUpdateAction(Request $request)
    {
        $settings = $this->getSettingService()->get('api', []);
        $settings['api_app_secret_key'] = $this->randomkeys('32');
        file_put_contents("/tmp/jc123", json_encode($settings), 8);
        $this->getSettingService()->set('api', $settings);

        return $this->redirect($this->generateUrl('admin_v2_api_setting', [], 0));
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}