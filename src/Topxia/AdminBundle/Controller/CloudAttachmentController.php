<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class CloudAttachmentController extends BaseController
{
    public function indexAction(Request $request)
    {
        try {
            $api    = CloudAPIFactory::create('leaf');
            $result = $api->get("/me");
        } catch (\RuntimeException $e) {
            return $this->render('TopxiaAdminBundle:CloudAttachment:api-error.html.twig', array());
        }

        $storageSetting = $this->getSettingService()->get('storage', array());

        if (isset($result['hasStorage']) && $result['hasStorage'] == '1' && $storageSetting['upload_mode'] == "cloud") {
            return $this->render('TopxiaAdminBundle:CloudAttachment:index.html.twig');
        }

        return $this->render('TopxiaAdminBundle:CloudAttachment:error.html.twig', array());
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }
}
