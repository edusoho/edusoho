<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;

class FeatureLabController extends BaseController
{
    public function settingAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $settings = $request->request->all();
            $this->getSettingService()->set('face', $settings);

            return $this->createJsonResponse(array('success' => 1));
        }

        return $this->render('admin/feature-lab/setting.html.twig', array(
        ));
    }

    public function faceIdentifyAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $settings = $request->request->all();
            $this->getSettingService()->set('face', $settings);

            return $this->createJsonResponse(array('success' => 1));
        }

        return $this->render('admin/feature-lab/face-identify.html.twig', array(
        ));
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
