<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class DiskController extends BaseController
{

    public function uploadCallbackAction (Request $request)
    {
        $data = $request->request->all();
        $data['storage'] = 'cloud';

        $file = $this->getDiskService()->addFile($data);

        return $this->createJsonResponse($file);
    }

    protected function getDiskService()
    {
        return $this->getServiceKernel()->createService('User.DiskService');
    }

}