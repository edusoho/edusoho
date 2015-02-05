<?php
namespace Topxia\AdminBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class ClassroomController extends BaseController
{

    public function IndexAction(Request $request)
    {
        $fields = $request->query->all();

        $conditions = array(
            'title'=>'',
        );

        if(!empty($fields)){
            $conditions =$fields;
        } 

        $paginator = new Paginator(
            $this->get('request'),
            $this->getClassroomService()->searchClassroomsCount($conditions),
            10
        );

        $classroomInfo=$this->getClassroomService()->searchClassrooms(
                $conditions,
                array('createdTime','desc'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
        );
        
        $classroomIds=ArrayToolkit::column($classroomInfo,'id');

        $coinPriceAll=array();
        $priceAll=array();
        $classroomCourses = array();
        $classroomCoursesNum = array();

        foreach ($classroomIds as $key => $value) {
            $classroomCourses= $this->getClassroomService()->getAllCourses($value);
            $classroomCoursesNum[$value] = count($classroomCourses);
           
            $courseIds=ArrayToolkit::column($classroomCourses,'courseId');
            $courses=$this->getCourseService()->findCoursesByIds($courseIds);
            $coinPrice=0;
            $price=0;
            foreach ($courses as $course) {
                $coinPrice+=$course['coinPrice'];
                $price+=$course['price'];
            }
            $coinPriceAll[$value] = $coinPrice;
            $priceAll[$value] = $price;
        }

// var_dump($priceAll);
// exit();

        return $this->render('TopxiaAdminBundle:Classroom:index.html.twig',array(
            'classroomInfo'=>$classroomInfo,
            'paginator' => $paginator,
            'classroomCoursesNum' =>$classroomCoursesNum,
            'priceAll'=>$priceAll,
            'coinPriceAll'=>$coinPriceAll,

            ));
    }

    public function setAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {

            $this->setFlashMessage('success',"班级设置成功！");

            $set=$request->request->all();

            $this->getSettingService()->set('classroom', $set);
        }

        return $this->render('TopxiaAdminBundle:Classroom:set.html.twig', array(
        ));
    }
    
    public function addClassroomAction(Request $request) 
    {
        if ($this->get('security.context')->isGranted('ROLE_ADMIN')!==true) {
            return $this->createMessageResponse('info', '目前只允许管理员创建班级!');
        }

        $user = $this->getCurrentUser();
  
        if (!$user->isLogin()) {
            return $this->createErrorResponse($request, 'not_login', '用户未登录，创建班级失败。');
        }

        if ($request->getMethod() == 'POST') {

            $myClassroom = $request->request->all();

            $title=trim($myClassroom['title']);
            if(empty($title)){
                $this->setFlashMessage('danger',"班级名称不能为空！");

                return $this->render("TopxiaAdminBundle:Classroom:classroomadd.html.twig");
            }

            $isClassroomExisted = $this->getClassroomService()->findClassroomByTitle($title);

            if (!empty($isClassroomExisted)) {
                $this->setFlashMessage('danger',"班级名称已被使用，创建班级失败！");

                return $this->render("TopxiaAdminBundle:Classroom:classroomadd.html.twig");
            }

            $classroom = array(
                'title' => $myClassroom['title'],
            );

            $classroom = $this->getClassroomService()->addClassroom($classroom);
            
            $this->setFlashMessage('success',"恭喜！创建班级成功！");
            
            return $this->redirect($this->generateUrl('classroom_manage',array('id'=>$classroom['id'])));
        }

        return $this->render("TopxiaAdminBundle:Classroom:classroomadd.html.twig");
    }

    public function  closeClassroomAction($id)
    {
        $this->getClassroomService()->closeClassroom($id);

        $classroom=$this->getClassroomService()->getClassroom($id);

        $coinPrice=0;
        $price=0;
        $coinPriceAll=array();
        $priceAll=array();
        $classroomCoursesNum =array();
        $classroomCourses= $this->getClassroomService()->getAllCourses($id);
        $classroomCoursesNum[$id] = count($classroomCourses);
       
        $courseIds=ArrayToolkit::column($classroomCourses,'courseId');
        $courses=$this->getCourseService()->findCoursesByIds($courseIds);
        foreach ($courses as $course) {
            $coinPrice+=$course['coinPrice'];
            $price+=$course['price'];
        }
        $coinPriceAll[$id] = $coinPrice;
        $priceAll[$id] = $price;
    
        return $this->render('TopxiaAdminBundle:Classroom:table-tr.html.twig', array(
            'classroom' => $classroom,
            'classroomCoursesNum'=>$classroomCoursesNum,
            'coinPriceAll'=>$coinPriceAll,
            'priceAll'=>$priceAll
        ));
    }

    public function openClassroomAction($id)
    {
        $this->getClassroomService()->publishClassroom($id);

        $classroom=$this->getClassroomService()->getClassroom($id);

        $coinPrice=0;
        $price=0;
        $coinPriceAll=array();
        $priceAll=array();
        $classroomCoursesNum =array();
        $classroomCourses= $this->getClassroomService()->getAllCourses($id);
        $classroomCoursesNum[$id] = count($classroomCourses);
       
        $courseIds=ArrayToolkit::column($classroomCourses,'courseId');
        $courses=$this->getCourseService()->findCoursesByIds($courseIds);
        foreach ($courses as $course) {
            $coinPrice+=$course['coinPrice'];
            $price+=$course['price'];
        }
        $coinPriceAll[$id] = $coinPrice;
        $priceAll[$id] = $price;

        return $this->render('TopxiaAdminBundle:Classroom:table-tr.html.twig', array(
            'classroom' => $classroom,
            'classroomCoursesNum'=>$classroomCoursesNum,
            'coinPriceAll'=>$coinPriceAll,
            'priceAll'=>$priceAll
        ));
    }

    public function deleteClassroomAction($id)
    {
        $this->getClassroomService()->deleteClassroom($id);
        return $this->createJsonResponse(true);
    }
    
    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom.ClassroomService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}