<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\Component\Payment\Payment;

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

    public function createAction(Request $request)
    {
        $fields = $request->request->all();

        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，创建订单失败。');
        }

        /* TODO set not null filed for order */
        $fields['targetId'] = 1;
        $fields['targetType'] = 'course';
        
        $goodIds = $fields['goodIds'];

        /* TOD only calculate the course type goods */
        $courseIds = array_unique(array_merge($goodIds['course']));
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $amount = 0;
        foreach ($courses as $course) {
            $amount += floatval($course['price']);
        }
        try {
            $accountPayable = floatval($fields["accountPayable"]);
            //价格比较
            if($amount != $accountPayable) {
                return $this->createMessageResponse('error', '支付价格不匹配，不能创建订单!');
            }

            /* TOD hardcode fields */
            $orderFileds = array(
                'priceType' => 'RMB',
                'totalPrice' => $accountPayable,
                'amount' => $amount,
                'coinRate' => 1,
                'coinAmount' => 0,
                'userId' => $user["id"],
                'payment' => 'alipay',
                'targetId' => $fields['targetId'],
                'coupon' => null,
                'couponDiscount' => null,
                'needInvoice' => $fields['needInvoice'],
                'shippingAddressId' => $fields['shippingAddressId'],
                'invoice' => array(
                    'title' => $fields['invoiceTitle'],
                    'type' => $fields['invoiceType'],
                    'comment' => $fields['invoiceComment'],
                    'userId' => $user["id"],
                    'amount' => $accountPayable,
                    'createdTime' => time()
                )
            );

            $order = $this->getCourseOrderService()->createOrder($orderFileds);

            return $this->redirect($this->generateUrl('pay_center_show', array(
                'id' => $order['id']
            )));
        } catch (\Exception $e) {
            return $this->createMessageResponse('error', $e->getMessage());
        }

    }


    public function payAction(Request $request)
    {
        return $this->render('CustomWebBundle:Order:order-finish.html.twig', array(
            'process' => 'finish'
        ));
    }

    public function submitPayRequestAction(Request $request , $order, $requestParams)
    {
        $paymentRequest = $this->createPaymentRequest($order, $requestParams);
        return $this->render('CustomWebBundle:Order:submit-pay-request.html.twig', array(
            'form' => $paymentRequest->form(),
            'order' => $order,
        ));
    }

    private function createPaymentRequest($order, $requestParams)
    {
        $options = $this->getPaymentOptions($order['payment']);
        $request = Payment::createRequest($order['payment'], $options);

        $requestParams = array_merge($requestParams, array(
            'orderSn' => $order['sn'],
            'title' => $order['title'],
            'summary' => '',
            'amount' => $order['amount'],
        ));
        return $request->setParams($requestParams);
    }

    private function getPaymentOptions($payment)
    {
        $settings = $this->setting('payment');

        if (empty($settings)) {
            throw new \RuntimeException('支付参数尚未配置，请先配置。');
        }

        if (empty($settings['enabled'])) {
            throw new \RuntimeException("支付模块未开启，请先开启。");
        }

        if (empty($settings[$payment. '_enabled'])) {
            throw new \RuntimeException("支付模块({$payment})未开启，请先开启。");
        }

        if (empty($settings["{$payment}_key"]) or empty($settings["{$payment}_secret"])) {
            throw new \RuntimeException("支付模块({$payment})参数未设置，请先设置。");
        }

        $options = array(
            'key' => $settings["{$payment}_key"],
            'secret' => $settings["{$payment}_secret"],
            'type' => $settings["{$payment}_type"]
        );

        return $options;
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

    private function getCourseOrderService()
    {
        return $this->getServiceKernel()->createService('Custom:Course.CourseOrderService');
    }
}