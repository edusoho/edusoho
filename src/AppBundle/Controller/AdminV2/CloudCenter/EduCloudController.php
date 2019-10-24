<?php

namespace AppBundle\Controller\AdminV2\CloudCenter;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\CloudPlatform\Service\EduCloudService;
use Biz\System\Service\SettingService;
use Biz\Util\EdusohoLiveClient;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\CloudPlatform\Service\AppService;
use Symfony\Component\HttpFoundation\Request;

class EduCloudController extends BaseController
{
    //概览页，服务概况页
    // refactor
    public function myCloudOverviewAction(Request $request)
    {
        //直播业务有用到只迁移了action需要后面修改云模块的修改
        try {
            $api = CloudAPIFactory::create('root');
            $isBinded = $this->getAppService()->getBinded();
            $overview = $api->get("/cloud/{$api->getAccessKey()}/overview");
        } catch (\RuntimeException $e) {
            return $this->render('admin/edu-cloud/cloud-error.html.twig', array());
        }
        if (!isset($overview['error'])) {
            $paidService = array();
            $unPaidService = array();
            $this->getSettingService()->set('cloud_status', array(
                'enabled' => $overview['enabled'],
                'locked' => $overview['locked'],
                'accessCloud' => $overview['accessCloud'],
            ));

            foreach ($overview['services'] as $key => $value) {
                if (true == $value) {
                    $paidService[] = $key;
                } else {
                    $unPaidService[] = $key;
                }
            }

            foreach ($unPaidService as $key => $value) {
                if ('search' == $value) {
                    unset($unPaidService[$key]);
                }
            }
        }

        return $this->render('admin/edu-cloud/overview/index.html.twig', array(
            'isBinded' => $isBinded,
            'overview' => $overview,
            'paidService' => isset($paidService) ? $paidService : false,
            'unPaidService' => isset($unPaidService) ? $unPaidService : false,
        ));
    }

    public function liveSettingAction(Request $request)
    {
        //直播业务有用到只迁移了action需要后面修改云模块的修改
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('admin/edu-cloud/live/trial.html.twig');
        }

        if (!$this->isVisibleCloud()) {
            return $this->redirect($this->generateUrl('admin_my_cloud_overview'));
        }

        $liveCourseSetting = $this->getSettingService()->get('live-course', array());
        $client = new EdusohoLiveClient();
        $capacity = $client->getCapacity();

        if ($request->isMethod('POST')) {
            try {
                $api = CloudAPIFactory::create('root');
                $overview = $api->get('/me/live/overview');
            } catch (\RuntimeException $e) {
                return $this->render('admin/edu-cloud/live-error.html.twig', array());
            }

            if (isset($overview['isBuy'])) {
                $this->setFlashMessage('danger', 'site.illegal.request');
            }

            $live = $request->request->all();
            $liveCourseSetting = array_merge($liveCourseSetting, $live);
            $liveCourseSetting['live_student_capacity'] = empty($capacity['capacity']) ? 0 : $capacity['capacity'];

            $courseSetting = $this->getSettingService()->get('course', array());
            $setting = array_merge($courseSetting, $liveCourseSetting);
            $this->getSettingService()->set('live-course', $liveCourseSetting);
            $this->getSettingService()->set('course', $setting);

            $this->setCloudLiveLogo($capacity['provider'], $client);

            $redirectUrl = 'talkFun' == $capacity['provider'] ? 'admin_setting_cloud_edulive' : 'admin_cloud_edulive_overview';

            return $this->redirect($this->generateUrl($redirectUrl));
        }

        if (empty($liveCourseSetting['live_course_enabled'])) {
            return $this->redirect($this->generateUrl('admin_cloud_edulive_overview'));
        }

        $liveEnabled = $liveCourseSetting['live_course_enabled'];
        if (null === $liveEnabled || 0 === $liveEnabled) {
            return $this->redirect($this->generateUrl('admin_cloud_edulive_overview'));
        }
        try {
            $api = CloudAPIFactory::create('root');
            $overview = $api->get('/me/live/overview');
        } catch (\RuntimeException $e) {
            return $this->render('admin/edu-cloud/live-error.html.twig', array());
        }

        return $this->render('admin/edu-cloud/live/setting.html.twig', array(
            'account' => $overview['account'],
            'liveCourseSetting' => $liveCourseSetting,
            'capacity' => $capacity,
        ));
    }

    protected function setCloudLiveLogo($provider, $client)
    {
        $setting = $this->getSettingService()->get('live-course', array());

        $isSetLogo = !empty($setting['webLogoPath']) || !empty($setting['appLogoPath']) || !empty($setting['logoUrl']);

        if ('talkFun' == $provider && $isSetLogo) {
            $logoData = array(
                'logoPcUrl' => empty($setting['webLogoPath']) ? '' : $setting['webLogoPath'],
                'logoClientUrl' => empty($setting['appLogoPath']) ? '' : $setting['appLogoPath'],
                'logoGotoUrl' => empty($setting['logoUrl']) ? 'http://www.talk-fun.com' : $setting['logoUrl'],
            );
            $result = $client->setLiveLogo($logoData);

            if (isset($result['error'])) {
                return $this->createMessageResponse('error', '设置直播logo出错');
            }
        }

        return true;
    }

    private function isVisibleCloud()
    {
        return $this->getEduCloudService()->isVisibleCloud();
    }

    /**
     * @return EduCloudService
     */
    protected function getEduCloudService()
    {
        return $this->createService('CloudPlatform:EduCloudService');
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
