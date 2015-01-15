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
        list($groupCarts, $itemResult) = $this->getCartsService()->findCurrentUserCarts();
        if($groupCarts) {
            $carts = $groupCarts['course'];
            $courses = $itemResult['course']['items'];
            $users = $itemResult['course']['extra']['users'];
        } else {
            $carts = array();
            $courses = array();
            $users = array();
        }
      

        $totalPrice = 0;
        foreach ($courses as $course) {
            $totalPrice +=  $course['price'];
        }

        return $this->render('CustomWebBundle:Carts:show-popover.html.twig',array(
            'carts' => $carts,
            'courses' => $courses,
            'users' => $users,
            'totalPrice' => $totalPrice
        ));
    }

    public function CartCoursesAction(Request $request)
    {
        $hotSales = $this->getCourseService()->findhotSaleCourses();
        $hotSaleCourses = array();
        if (!empty($hotSales)){
            $courseIds = ArrayToolkit::column($hotSales,'courseId');
            $hotSaleCourses = $this->getCourseService()->findCoursesByIds($courseIds);
        }

        $user = $this->getCurrentUser();
        $favoritedCourses = $this->getCourseService()->findUserFavoritedCourses($user['id'],0,10);

        return $this->render('CustomWebBundle:Carts:course-list.html.twig',array(
            'hotSaleCourses' => $hotSaleCourses,
            'favoritedCourses' => $favoritedCourses
        ));
    }

    public function deleteAction(Request $request)
    {
        $ids = $request->request->get('ids', array());

        $id = $request->query->get('id', null);

        if ($id) {
            array_push($ids, $id);
        }

        $res = $this->getCartsService()->deleteCartsByIds($ids);

        if ($res) {
            return $this->createJsonResponse(array('status'=>'success'));
        } else {
            return $this->createJsonResponse(array('status'=>'fail'));
        }
    }

    public function listAction(Request $request)
    {
        $userId = $this->getCurrentUser()->id;
        list($groupCarts, $itemResult) = $this->getCartsService()->findCurrentUserCarts();
        $favoritedTotal = $this->getCourseService()->findUserFavoritedCourseCount($userId);
        $favoritedCourses = $this->getCourseService()->findUserFavoritedCourses($userId,0,$favoritedTotal);
        $favoritedCourses = ArrayToolkit::index($favoritedCourses,'id');

        return $this->render('CustomWebBundle:Carts:list.html.twig',array(
            'groupCarts' => $groupCarts,
            'itemResult' => $itemResult,
            'favoritedCourses' => $favoritedCourses
        ));
    }

    public function favoriteAction(Request $request)
    {
        $params = $request->request->all();
        if (!empty($params['ids'])) {
            $ids = $params['ids'];
        } else {
            return $this->createJsonResponse(array('status' => 'fail'));
        }

        $this->getCourseService()->favoriteCourses($ids);

        return $this->createJsonResponse(true);
    }

    public function addAction(Request $request)
    {
        $id = $request->query->get('id');
        $itemType = $request->query->get('itemType','course');

        if (empty($id)) {
            return $this->createNotFoundException();
        }
        $user = $this->getCurrentUser();

        $cart = array(
            'itemId' => $id,
            'itemType' => $itemType,
            'number' => 1,
            'userKey' => $_COOKIE['user-key'],
            'createdTime' => time()
        );

        $carts = $this->getCartsService()->searchCarts($cart,array('createdTime','DESC'),0,1);

        if (empty($carts)) {
            $carts = $this->getCartsService()->addCart($cart);
            return $this->createJsonResponse(array('status' => 'success'));
        }

        return $this->createJsonResponse(array('status' => 'exists'));
    }

    private function getUsers($userIds)
    {
        $ids = array();

        foreach ($userIds as $key => $userId) {
            foreach ($userId as $key => $value) {
                $ids[] = $value;
            }
        }
        $ids = array_unique($ids);
        $ids = array_values($ids);

        return $this->getUserService()->findUsersByIds($ids);
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