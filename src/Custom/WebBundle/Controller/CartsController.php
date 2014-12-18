<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class CartsController extends BaseController
{
    public function showAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $carts = $this->getCartsService()->searchCartsByUseId($user['id'],0,3);
        $courseIds = ArrayToolkit::culum($carts['itemId']);
        $course = $this->getCourseService()->findCoursesByCourseIds($courseIds);

        return $this->render('CustomWebBundle:Carts:show-popover.html.twig',array(
            'carts' => $carts
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
}