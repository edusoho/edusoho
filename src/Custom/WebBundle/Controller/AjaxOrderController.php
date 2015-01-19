<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class AjaxOrderController extends BaseController
{
    public function updateUserInvoiceAction(Request $request, $userId, $id)
    {
        $title = $request->request->get('title');
        if(empty($id)) {
            $newInvoice = array(
                'userId' => $userId,
                'title' => $title,
                'createdTime' => time()
            );
            $this->getUserInvoiceService()->createUserInvoice($newInvoice);
        } else {
            $this->getUserInvoiceService()->updateUserInvoice($id, array('title' => $title));
        }
        return $this->createJsonResponse(true);
    }

    private function getUserInvoiceService()
    {
        return $this->getServiceKernel()->createService('Custom:Order.UserInvoiceService');
    }

}