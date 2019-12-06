<?php

namespace AppBundle\Controller\AdminV2\CloudCenter;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\CloudPlatform\Service\EduCloudService;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;

class CloudServerController extends BaseController
{
    public function smsOverviewAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('admin-v2/cloud-center/cloud-server/sms/trial.html.twig');
        }

        if (!($this->isVisibleCloud())) {
            return $this->redirect($this->generateUrl('admin_my_cloud_overview'));
        }

        $settings = $this->getSettingService()->get('storage', array());
        if (empty($settings['cloud_access_key']) || empty($settings['cloud_secret_key'])) {
            $this->setFlashMessage('warning', 'admin.cloud.license.has_no_license');

            return $this->redirect($this->generateUrl('admin_setting_cloud_key_update'));
        }

        $cloudSmsSettings = $this->getSettingService()->get('cloud_sms', array());
        try {
            $api = CloudAPIFactory::create('root');
            $overview = $api->get('/me/sms/overview');
            $smsInfo = $api->get('/me/sms_account');
            $this->checkSmsSign($smsInfo);
        } catch (\RuntimeException $e) {
            return $this->render('admin-v2/cloud-center/edu-cloud/sms-error.html.twig', array());
        }
        $isSmsWithoutEnable = $this->isSmsWithoutEnable($overview, $cloudSmsSettings);
        if ($isSmsWithoutEnable) {
            $overview['isBuy'] = isset($overview['isBuy']) ? $overview['isBuy'] : true;

            return $this->render('admin-v2/cloud-center/edu-cloud/sms/without-enable.html.twig', array(
                'overview' => $overview,
                'cloudSmsSettings' => $cloudSmsSettings,
            ));
        }
        $chartData = $this->dealChartData($overview['data']);

        return $this->render('admin-v2/cloud-center/edu-cloud/sms/overview.html.twig', array(
            'account' => $overview['account'],
            'chartData' => $chartData,
            'smsInfo' => $smsInfo,
        ));
    }

    protected function checkSmsSign($smsInfo)
    {
        if (empty($smsInfo)) {
            $smsSignUrl = $this->generateUrl('admin_v2_cloud_sms_sign');
            $this->setFlashMessage('danger',
                "尚未开通云短信,不能发送短信, <a href='{$smsSignUrl}' class='plm' target='_blank'>去设置</a>");
        } else {
            if (empty($smsInfo['name']) && empty($smsInfo['isExistSmsSign'])) {
                $smsSignUrl = $this->generateUrl('admin_v2_cloud_sms_sign');
                $this->setFlashMessage('danger',
                    "尚未设置短信签名,不能发送短信, <a href='{$smsSignUrl}' class='plm' target='_blank'>去设置</a>");
            }
            if (empty($smsInfo['name']) && !empty($smsInfo['isExistSmsSign']) && null == $smsInfo['usedSmsSign']) {
                $this->setFlashMessage('danger', 'admin.cloud.sms.signature_in_reviewing');
            }
        }
    }

    /**
     * @return \AppBundle\Twig\WebExtension|object
     */
    protected function getWebExtension()
    {
        return $this->get('web.twig.extension');
    }

    /**
     * @return EduCloudService
     */
    protected function getEduCloudService()
    {
        return $this->createService('CloudPlatform:EduCloudService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    private function isVisibleCloud()
    {
        return $this->getEduCloudService()->isVisibleCloud();
    }

    private function isSmsWithoutEnable($overview, $cloudSmsSettings)
    {
        $isSmsWithoutEnable = (isset($overview['isBuy']) && false == $overview['isBuy']) || (isset($cloudSmsSettings['sms_enabled']) && 0 == $cloudSmsSettings['sms_enabled']) || !isset($cloudSmsSettings['sms_enabled']);

        return $isSmsWithoutEnable;
    }

    private function dealChartData($data)
    {
        $chartData['unit'] = $data['unit'];
        if (empty($data['items'])) {
            for ($i = 7; $i > 0; --$i) {
                $chartData['date'][] = date('Y-m-d', strtotime('-'.$i.'days'));
                $chartData['count'][] = 0;
            }

            return $chartData;
        }

        foreach ($data['items'] as $item) {
            $chartData['date'][] = $item['date'];
            $chartData['count'][] = $item['count'];
        }

        return $chartData;
    }
}
