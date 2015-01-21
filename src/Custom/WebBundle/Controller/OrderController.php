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

        list($groupCarts, $itemResult) = $this->getCartsService()->findCurrentUserCarts();

        $type = '';
        $targetId = '';
        $referer = !empty($data['referer']) ? $data['referer'] : 'cartsAction';

        if (!empty($data['referer']) && $referer == 'showAction') {

            $groupCarts = array();
            $targetType = !empty($data['targetType']) ? $data['targetType'] : 'course';
            $targetId = !empty($data['targetId']) ? $data['targetId'] : '';

            if (empty($targetId)) {
                return $this->createMessageResponse('error', 'targetId不能为空！');
            }

            $type = 'course';

            $course = $this->getCourseService()->getCourse($targetId);
            $itemResult = array();
            $itemResult['course']['items'][$targetId] = $course;

            $teacherIds = array_values(array_unique($course['teacherIds']));
            $teachers = $this->getUserService()->findUsersByIds($teacherIds);
            $itemResult['course']['extra']['users'] = $teachers;

            $subcourses = $this->getCourseService()->findSubcoursesByCourseId($targetId);
            $subcourses = $subcourses[1];
            $itemResult[$targetId]['subcourses'] = $subcourses;

            $totalPrice = $this->getCourseTotalPrice($subcourses);
            $itemResult['course']['items'][$targetId]['costPrice'] = $totalPrice;
        }

        return $this->render('CustomWebBundle:Order:order-verify.html.twig',array(
            'profile' => $profile,
            'invoices' => $invoices,
            'itemResult' => $itemResult,
            'groupCarts' => $groupCarts,
            'role' => 'verify',
            'referer' => $referer,
            'type' => $type,
            'targetId' => $targetId
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
    
    private function getCourseTotalPrice($courses)
    {
        $totalPrice = 0;

        foreach ($courses as $key => $course) {
            $totalPrice += floatval($course['price']);
        }

        return $totalPrice;
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
}