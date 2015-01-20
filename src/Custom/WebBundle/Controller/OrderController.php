<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class OrderController extends BaseController
{
    public function verifyAction(Request $request)
    {
        $user = $this->getCurrentUser();


        if (!$user->isLogin()) {
            return $this->createMessageResponse('info', '你好像忘了登录哦？', null, 3000, $this->generateUrl('login'));
        }

        $data = $request->request;
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $invoices = $this->getUserInvoiceService()->findUserInvoicesByUserId($user['id']);
        $shippingAddress = $this->getShippingAddressService()->getDefaultShippingAddressByUserId($user['id']);
        $userId = $this->getCurrentUser()->id;
        list($groupCarts, $itemResult) = $this->getCartsService()->findCurrentUserCarts();

        return $this->render('CustomWebBundle:Order:order-verify.html.twig',array(
            'profile' => $profile,
            'invoices' => $invoices,
            'shippingAddress' => $shippingAddress,
            'itemResult' => $itemResult,
            'groupCarts' => $groupCarts,
            'role' => 'verify'
        ));
    }

    public function userVerifyAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $profile['title'] = $user['title'];

     
        return $this->render('CustomWebBundle:Order:user-verify.html.twig',array(
           'profile' => $profile
        ));
    }
    

    private function getCartsService()
    {
        return $this->getServiceKernel()->createService('Custom:Carts.CartsService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getUserInvoiceService()
    {
        return $this->getServiceKernel()->createService('Custom:Order.UserInvoiceService');
    }

    private function getShippingAddressService()
    {
        return $this->getServiceKernel()->createService('Custom:Address.ShippingAddressService');
    }
}