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
        if (empty($user['id'])) {
            if (empty($_COOKIE['user-key'])){
                $carts = array();
            } else {
                $userKey = $_COOKIE['user-key'];
                $carts = $this->getCartsService()->findCartsByUserKey($userKey);
            }
        } else {
            $carts = $this->getCartsService()->findCartsByUserId($user['id']);
        }

        $courses = array();
        $users = array();

        if (!empty($carts)){
            $courseIds = ArrayToolkit::column($carts,'itemId');
            $courses = $this->getCourseService()->findCoursesByIds($courseIds);
            $courses = ArrayToolkit::index($courses,'id');
            $teacherIds = ArrayToolkit::column($courses,'teacherIds');
            $users = $this->getUsers($teacherIds);
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

        $condition = array(
            'userId' => $userId,
        );

        $carts = $this->getCartsService()->searchCarts(
            $condition,
            array('createdTime','DESC'),
            0,
            $this->getCartsService()->searchCartsCount($condition)
        );
var_dump('111');
        $ids = ArrayToolkit::column($carts,'itemId');
        $courses = $this->getCourseService()->findCoursesByIds($ids);
var_dump('222');
        $teacherIds = ArrayToolkit::column($courses,'teacherIds');
        $users = $this->getUsers($teacherIds);
var_dump('333');exit();
        $favoritedTotal = $this->getCourseService()->findUserFavoritedCourseCount($userId);
        $favoritedCourses = $this->getCourseService()->findUserFavoritedCourses($userId,0,$favoritedTotal);
        $favoritedCourses = ArrayToolkit::index($favoritedCourses,'id');

        return $this->render('CustomWebBundle:Carts:list.html.twig',array(
            'carts' => $carts,
            'courses' =>$courses,
            'users' => $users,
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
            'userId' => $user->id,
            'createdTime' => time()
        );

        if (!$user->isLogin()) {
            $Uuid = $_COOKIE['user-key'];
            $cart['userKey'] = $Uuid;
        }

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