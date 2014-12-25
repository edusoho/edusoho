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
        $carts = $this->getCartsService()->findCartsByUseId($user['id']);
        array_slice($carts,0,5);
        $courses = array();
        $users = array();
        
        if (!empty($carts)){
            $courseIds = ArrayToolkit::column($carts,'itemId');
            $courses = $this->getCourseService()->findCoursesByIds($courseIds);
            $courses = ArrayToolkit::index($courses,'id');
            $teacherIds = ArrayToolkit::column($courses,'teacherIds');
            $users = $this->getUsers($teacherIds);
        }

        return $this->render('CustomWebBundle:Carts:show-popover.html.twig',array(
            'carts' => $carts,
            'courses' => $courses,
            'users' => $users,
        ));
    }

    public function hotSaleAction(Request $request)
    {
        $courses = $this->getCourseService()->findhotSaleCourses();
        return $this->render('CustomWebBundle:Carts:show-popover.html.twig',array(
            'courses' =>$courses,
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

        $ids = ArrayToolkit::column($carts,'itemId');
        $courses = $this->getCourseService()->findCoursesByIds($ids);
        $teacherIds = ArrayToolkit::column($courses,'teacherIds');
        $users = $this->getUsers($teacherIds);

        return $this->render('CustomWebBundle:Carts:list.html.twig',array(
            'carts' => $carts,
            'courses' =>$courses,
            'users' => $users,
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

            $Uuid = $this->getUuid();
            if (empty($_COOKIE['user-key'])) {
                setcookie('user-key',$Uuid);
                $cart['userKey'] = $Uuid;
            } else {
                $Uuid = $_COOKIE['user-key'];
                $cart['userKey'] = $Uuid;
            }
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

    private function getUuid($prefix = "")
    {
        $str = md5(uniqid(mt_rand(), true));

        $uuid  = substr($str,0,8) . '-';
        $uuid .= substr($str,8,4) . '-';
        $uuid .= substr($str,12,4) . '-';
        $uuid .= substr($str,16,4) . '-';
        $uuid .= substr($str,20,12);

        return $prefix . $uuid;
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