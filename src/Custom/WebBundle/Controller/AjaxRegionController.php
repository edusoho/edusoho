<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class AjaxRegionController extends BaseController
{
    public function getJsonAction(Request $request, $json)
    {
        $jsonFile = $this->getServiceKernel()->getParameter('kernel.root_dir') . '/../src/Custom/WebBundle/Resources/data/region/' . $json;
        $string = file_get_contents($jsonFile);
        $new = json_decode($string, true);
        return $this->createJsonResponse($new);
    }

    private function getUserInvoiceService()
    {
        return $this->getServiceKernel()->createService('Custom:Order.UserInvoiceService');
    }

}