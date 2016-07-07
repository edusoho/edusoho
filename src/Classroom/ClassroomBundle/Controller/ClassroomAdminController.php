<?php
namespace Classroom\ClassroomBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\AdminBundle\Controller\BaseController;

class ClassroomAdminController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();

        $conditions = $this->fillOrgCode($conditions);
        $paginator  = new Paginator(
            $this->get('request'),
            $this->getClassroomService()->searchClassroomsCount($conditions),
            10
        );

        $classroomInfo = $this->getClassroomService()->searchClassrooms(
            $conditions,
            array('createdTime', 'desc'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $classroomIds = ArrayToolkit::column($classroomInfo, 'id');

        $coinPriceAll        = array();
        $priceAll            = array();
        $classroomCoursesNum = array();

        $cashRate = $this->getCashRate();

        foreach ($classroomIds as $key => $value) {
            $courses                     = $this->getClassroomService()->findActiveCoursesByClassroomId($value);
            $classroomCoursesNum[$value] = count($courses);

            $coinPrice = 0;
            $price     = 0;

            foreach ($courses as $course) {
                $coinPrice += $course['price'] * $cashRate;
                $price += $course['price'];
            }

            $coinPriceAll[$value] = $coinPrice;
            $priceAll[$value]     = $price;
        }

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($classroomInfo, 'categoryId'));

        return $this->render('ClassroomBundle:ClassroomAdmin:index.html.twig', array(
            'classroomInfo'       => $classroomInfo,
            'paginator'           => $paginator,
            'classroomCoursesNum' => $classroomCoursesNum,
            'priceAll'            => $priceAll,
            'coinPriceAll'        => $coinPriceAll,
            'categories'          => $categories
        ));
    }

    public function setAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $this->setFlashMessage('success', "班级设置成功！");

            $set = $request->request->all();

            $this->getSettingService()->set('classroom', $set);
        }

        return $this->render('ClassroomBundle:ClassroomAdmin:set.html.twig', array(
        ));
    }

    public function addClassroomAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createErrorResponse($request, 'not_login', '用户未登录，创建班级失败。');
        }

        if (!$user->isAdmin()) {
            return $this->createMessageResponse('info', '只允许管理员创建班级!');
        }

        if ($request->getMethod() == 'POST') {
            $myClassroom = $request->request->all();

            $title = trim($myClassroom['title']);

            if (empty($title)) {
                $this->setFlashMessage('danger', "班级名称不能为空！");

                return $this->render("ClassroomBundle:ClassroomAdmin:classroomadd.html.twig");
            }

            $isClassroomExisted = $this->getClassroomService()->findClassroomByTitle($title);

            if (!empty($isClassroomExisted)) {
                $this->setFlashMessage('danger', "班级名称已被使用，创建班级失败！");

                return $this->render("ClassroomBundle:ClassroomAdmin:classroomadd.html.twig");
            }

            if (!array_key_exists('buyable', $myClassroom)) {
                $myClassroom['buyable'] = 0;
            }

            $classroom = array(
                'title'    => $myClassroom['title'],
                'showable' => $myClassroom['showable'],
                'buyable'  => $myClassroom['buyable']
            );

            if (array_key_exists('orgCode', $myClassroom)) {
                $classroom['orgCode'] = $myClassroom['orgCode'];
            }

            $classroom = $this->getClassroomService()->addClassroom($classroom);

            $this->setFlashMessage('success', "恭喜！创建班级成功！");

            return $this->redirect($this->generateUrl('classroom_manage', array('id' => $classroom['id'])));
        }

        return $this->render("ClassroomBundle:ClassroomAdmin:classroomadd.html.twig");
    }

    public function closeClassroomAction($id)
    {
        $this->getClassroomService()->closeClassroom($id);

        $classroom = $this->getClassroomService()->getClassroom($id);

        return $this->renderClassroomTr($id, $classroom);
    }

    public function openClassroomAction($id)
    {
        $this->getClassroomService()->publishClassroom($id);

        $classroom = $this->getClassroomService()->getClassroom($id);

        return $this->renderClassroomTr($id, $classroom);
    }

    public function deleteClassroomAction($id)
    {
        $this->getClassroomService()->deleteClassroom($id);

        return $this->createJsonResponse(true);
    }

    public function recommendAction(Request $request, $id)
    {
        $classroom = $this->getClassroomService()->getClassroom($id);
        $ref       = $request->query->get('ref');

        if ($request->getMethod() == 'POST') {
            $number = $request->request->get('number');

            $classroom = $this->getClassroomService()->recommendClassroom($id, $number);

            if ($ref == 'recommendList') {
                return $this->render('ClassroomBundle:ClassroomAdmin:recommend-tr.html.twig', array(
                    'classroom' => $classroom
                ));
            }

            return $this->renderClassroomTr($id, $classroom);
        }

        return $this->render('ClassroomBundle:ClassroomAdmin:recommend-modal.html.twig', array(
            'classroom' => $classroom,
            'ref'       => $ref
        ));
    }

    public function cancelRecommendAction(Request $request, $id)
    {
        $classroom = $this->getClassroomService()->cancelRecommendClassroom($id);
        $ref       = $request->query->get('ref');

        if ($ref == 'recommendList') {
            return $this->render('ClassroomBundle:ClassroomAdmin:recommend-tr.html.twig', array(
                'classroom' => $classroom
            ));
        }

        return $this->renderClassroomTr($id, $classroom);
    }

    public function recommendListAction()
    {
        $conditions = array(
            'status'      => 'published',
            'recommended' => 1
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getClassroomService()->searchClassroomsCount($conditions),
            20
        );

        $classrooms = $this->getClassroomService()->searchClassrooms(
            $conditions,
            array('recommendedSeq', 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($classrooms, 'userId'));

        return $this->render('ClassroomBundle:ClassroomAdmin:recommend-list.html.twig', array(
            'classrooms' => $classrooms,
            'users'      => $users,
            'paginator'  => $paginator,
            'ref'        => 'recommendList'
        ));
    }

    public function chooserAction(Request $request)
    {
        $conditions             = $request->query->all();
        $conditions["parentId"] = 0;

        if (isset($conditions["categoryId"]) && $conditions["categoryId"] == "") {
            unset($conditions["categoryId"]);
        }

        if (isset($conditions["status"]) && $conditions["status"] == "") {
            unset($conditions["status"]);
        }

        if (isset($conditions["title"]) && $conditions["title"] == "") {
            unset($conditions["title"]);
        }

        $count = $this->getClassroomService()->searchClassroomsCount($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $count,
            20
        );

        $classrooms = $this->getClassroomService()->searchClassrooms(
            $conditions,
            array('createdTime', 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($classrooms, 'categoryId'));

        return $this->render('ClassroomBundle:ClassroomAdmin:classroom-chooser.html.twig', array(
            'conditions' => $conditions,
            'classrooms' => $classrooms,
            'categories' => $categories,
            'paginator'  => $paginator
        ));
    }

    private function renderClassroomTr($id, $classroom)
    {
        $coinPrice                = 0;
        $price                    = 0;
        $coinPriceAll             = array();
        $priceAll                 = array();
        $classroomCoursesNum      = array();
        $courses                  = $this->getClassroomService()->findActiveCoursesByClassroomId($id);
        $classroomCoursesNum[$id] = count($courses);
        $cashRate                 = $this->getCashRate();

        foreach ($courses as $course) {
            $coinPrice += $course['price'] * $cashRate;
            $price += $course['price'];
        }

        $coinPriceAll[$id] = $coinPrice;
        $priceAll[$id]     = $price;

        return $this->render('ClassroomBundle:ClassroomAdmin:table-tr.html.twig', array(
            'classroom'           => $classroom,
            'classroomCoursesNum' => $classroomCoursesNum,
            'coinPriceAll'        => $coinPriceAll,
            'priceAll'            => $priceAll
        ));
    }

    protected function getCashRate()
    {
        $coinSetting = $this->getSettingService()->get("coin");
        $coinEnable  = isset($coinSetting["coin_enabled"]) && $coinSetting["coin_enabled"] == 1;
        $cashRate    = $coinEnable && isset($coinSetting['cash_rate']) ? $coinSetting["cash_rate"] : 1;
        return $cashRate;
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }
}
