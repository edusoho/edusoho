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

        $data = $request->query->all();

        $profile = $this->getUserService()->getUserProfile($user['id']);
        $invoices = $this->getUserInvoiceService()->findUserInvoicesByUserId($user['id']);

        $shippingAddress = $this->getShippingAddressService()->getDefaultShippingAddressByUserId($user['id']);
        $userId = $this->getCurrentUser()->id;
        list($groupCarts, $itemResult) = $this->getCartsService()->findCurrentUserCarts();

        $cartIds = $request->request->get('cartIds',array());
        if (!empty($cartIds)) {
            $groupCarts = $this->filterGroupCarts($groupCarts,$cartIds);
        }

        $type = '';
        $targetId = '';
        $referer = !empty($data['referer']) ? $data['referer'] : 'cartsAction';

        if ($referer == 'showAction') {
            $groupCarts = array();
            $targetType = !empty($data['targetType']) ? $data['targetType'] : 'course';
            $targetId = !empty($data['targetId']) ? $data['targetId'] : '';

            if (empty($targetId)) {
                return $this->createMessageResponse('error', 'targetId不能为空！');
            }

            $type = 'course';

            $itemResult = $this->getItemResultBytargetId($targetId);
        }

        return $this->render('CustomWebBundle:Order:order-verify.html.twig',array(
            'profile' => $profile,
            'invoices' => $invoices,
            'shippingAddress' => $shippingAddress,
            'itemResult' => $itemResult,
            'groupCarts' => $groupCarts,
            'role' => 'verify',
            'process' => 'verify',
            'referer' => $referer,
            'type' => $type,
            'targetId' => $targetId,
            'cartIds' => $cartIds
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
    
    private function getItemResultBytargetId($targetId)
    {
        $course = $this->getCourseService()->getCourse($targetId);
        $itemResult = array();
        $itemResult['course']['items'][$targetId] = $course;

        $subcourses = $this->getCourseService()->findSubcoursesByCourseId($targetId);
        $subcourses = $subcourses[1];
        $itemResult[$targetId]['subcourses'] = $subcourses;

        list($totalPrice,$teacherIds) = $this->getCourseTotalPriceAndUserIds($course,$subcourses);
        $teacherIds = array_values(array_unique($teacherIds));
        $teachers = $this->getUserService()->findUsersByIds($teacherIds);
        $itemResult['course']['extra']['users'] = $teachers;
        $itemResult['course']['items'][$targetId]['costPrice'] = $totalPrice;

        return $itemResult;
    }

    private function filterGroupCarts($groupCarts,$cartIds)
    {
        $groupCarts = ArrayToolkit::index($groupCarts['course'],'id');
        $groupCartIds = ArrayToolkit::column($groupCarts,'id');
        $cartIds = explode(',', $cartIds);

        $newGroupCarts = array();
        foreach ($groupCartIds as $key => $groupCartId) {
            if (!in_array($groupCartId, $cartIds)) {
                unset($groupCarts[$groupCartId]);
            }
        }
        $newGroupCarts['course'] = $groupCarts;

        return $newGroupCarts;
    }

    public function submitAction(Request $request)
    {
        return $this->render('CustomWebBundle:Order:order-payment.html.twig', array(
            'process' => 'payment'
        ));
    }


    public function payAction(Request $request)
    {
        return $this->render('CustomWebBundle:Order:order-finish.html.twig', array(
            'process' => 'finish'
        ));
    }

    private function getCourseTotalPriceAndUserIds($course,$subcourses)
    {
        $totalPrice = 0;
        $teacherIds = array();

        foreach ($subcourses as $key => $subcourse) {
            $totalPrice += floatval($subcourse['price']);
            $teacherIds = array_merge($teacherIds,$subcourse['teacherIds']);
        }

        $teacherIds = array_merge($teacherIds,$course['teacherIds']);

        return array(
            $totalPrice,
            $teacherIds
        );
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