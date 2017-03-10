<?php

namespace AppBundle\Controller\Admin;

use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;
use Biz\CloudPlatform\CloudAPIFactory;

class CloudAttachmentController extends BaseController
{
    public function indexAction(Request $request)
    {
        try {
            $api = CloudAPIFactory::create('leaf');
            $result = $api->get('/me');
        } catch (\RuntimeException $e) {
            return $this->render('admin/cloud-attachment/api-error.html.twig', array());
        }

        $storageSetting = $this->getSettingService()->get('storage', array());

        if (isset($result['hasStorage']) && $result['hasStorage'] == '1' && $storageSetting['upload_mode'] == 'cloud') {
            return $this->render('admin/cloud-attachment/index.html.twig');
        }

        return $this->render('admin/cloud-attachment/error.html.twig', array());
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
