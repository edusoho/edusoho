<?php

namespace AppBundle\Controller\AdminV2\CloudCenter;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\System\Service\SettingService;
use Biz\System\SettingException;
use Symfony\Component\HttpFoundation\Request;

class FeatureLabController extends BaseController
{
    public function settingAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $settings = $request->request->all();
            $this->getSettingService()->set('feature', $settings);

            return $this->createJsonResponse(array('success' => 1));
        }

        $cloudInfo = $this->container->get('web.twig.data_extension')->getCloudInfo();

        return $this->render('admin-v2/cloud-center/feature-lab/setting.html.twig', array(
        ));
    }

    public function faceIdentifyAction(Request $request)
    {
        $featureSetting = $this->getSettingService()->get('feature', array());
        $cloudInfo = $this->container->get('web.twig.data_extension')->getCloudInfo();

        if (!isset($cloudInfo['ai.face']) || !$cloudInfo['ai.face'] || !isset($featureSetting['face_enabled']) || !$featureSetting['face_enabled']) {
            $this->createNewException(SettingException::AI_FACE_DISABLE());
        }

        if ('POST' == $request->getMethod()) {
            $settings = $request->request->all();
            $savedSetting = $this->getSettingService()->get('face');
            $savedSetting = array_merge($savedSetting, $settings);
            $this->getSettingService()->set('face', $savedSetting);

            return $this->createJsonResponse(array('success' => 1));
        }

        return $this->render('admin-v2/cloud-center/feature-lab/face-identify.html.twig', array(
        ));
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
