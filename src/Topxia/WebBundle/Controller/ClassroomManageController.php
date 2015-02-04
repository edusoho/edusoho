<?php
namespace Topxia\WebBundle\Controller;
use Topxia\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\FileToolkit;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;

class ClassroomManageController extends BaseController
{   
    public function indexAction($id)
    {   
        $classroom=$this->getClassroomService()->getClassroom($id);

        $courses=$this->getClassroomService()->getAllCourses($id);
        $coursesCount=count($courses);

        return $this->render("TopxiaWebBundle:ClassroomManage:index.html.twig",array(
            'classroom'=>$classroom,
            'coursesCount'=>$coursesCount));
    }

    public function studentsAction(Request $request,$id,$role='student')
    {   
        $classroom=$this->getClassroomService()->getClassroom($id);

        $fields = $request->query->all();
        $nickname="";
        if(isset($fields['nickName'])){
            $nickname =$fields['nickName'];
        } 

        $paginator = new Paginator(
            $request,
            $this->getClassroomService()->searchMemberCount(array('classId'=>$id,'role'=>'student','nickname'=>$nickname)),
            20
        );

        $students = $this->getClassroomService()->searchMembers(
            array('classId'=>$id,'role'=>'student','nickname'=>$nickname),
            array('createdTime','DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        // var_dump($students);exit();
        $studentUserIds = ArrayToolkit::column($students, 'userId');
        $users = $this->getUserService()->findUsersByIds($studentUserIds);

        // $progresses = array();
        // foreach ($students as $student) {
        //     $progresses[$student['userId']] = $this->calculateUserLearnProgress($course, $student);
        // }




        return $this->render("TopxiaWebBundle:ClassroomManage:student.html.twig",array(
            'classroom'=>$classroom,
            'students' => $students,
            'users'=>$users,
            // 'progresses' => $progresses,
            'paginator' => $paginator,
            'role'=>$role
            ));
    }

    public function aduitorAction(Request $request,$id,$role='aduitor')
    {   
        $classroom=$this->getClassroomService()->getClassroom($id);

        $fields = $request->query->all();
        $nickname="";
        if(isset($fields['nickName'])){
            $nickname =$fields['nickName'];
        } 

        $paginator = new Paginator(
            $request,
            $this->getClassroomService()->searchMemberCount(array('classId'=>$id,'role'=>'aduitor','nickname'=>$nickname)),
            20
        );

        $students = $this->getClassroomService()->searchMembers(
            array('classId'=>$id,'role'=>'aduitor','nickname'=>$nickname),
            array('createdTime','DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        // var_dump($students);exit();
        $studentUserIds = ArrayToolkit::column($students, 'userId');
        $users = $this->getUserService()->findUsersByIds($studentUserIds);

        return $this->render("TopxiaWebBundle:ClassroomManage:aduitor.html.twig",array(
            'classroom'=>$classroom,
            'students' => $students,
            'users'=>$users,
            'paginator' => $paginator,
            'role'=>$role
            ));
    }

    public function remarkAction(Request $request, $classroomId, $userId)
    {
        // $course = $this->getCourseService()->tryManageCourse($classroomId);
        $classroom=$this->getClassroomService()->getClassroom($classroomId);
        $user = $this->getUserService()->getUser($userId);
        $member = $this->getClassroomService()->getClassroomMember($classroomId, $userId);

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();
            $member = $this->getClassroomService()->remarkStudent($classroom['id'], $user['id'], $data['remark']);
            return $this->createStudentTrResponse($classroom, $member);
        }

        return $this->render('TopxiaWebBundle:ClassroomManage:remark-modal.html.twig',array(
            'member'=>$member,
            'user'=>$user,
            'classroom'=>$classroom,
        ));
    }

    private function createStudentTrResponse($classroom, $student)
    {
        // $courseSetting = $this->getSettingService()->get('course', array());
        // $isTeacherAuthManageStudent = !empty($courseSetting['teacher_manage_student']) ? 1: 0;

        $user = $this->getUserService()->getUser($student['userId']);
        // $progress = $this->calculateUserLearnProgress($course, $student);

        return $this->render('TopxiaWebBundle:ClassroomManage:tr.html.twig', array(
            'classroom' => $classroom,
            'student' => $student,
            'user'=>$user,
            'role'=>$student["role"],
            // 'progress' => $progress,
            // 'isTeacherAuthManageStudent' => $isTeacherAuthManageStudent,
        ));
    }

    public function removeAction(Request $request, $classroomId, $userId)
    {
        // $courseSetting = $this->getSettingService()->get('course', array());

        // if (!empty($courseSetting['teacher_manage_student'])) {
        //     $course = $this->getCourseService()->tryManageCourse($courseId);
        // } else {
        //     $course = $this->getCourseService()->tryAdminCourse($courseId);
        // }
        $classroom=$this->getClassroomService()->getClassroom($classroomId);

        $this->getClassroomService()->removeStudent($classroomId, $userId);

        $this->getNotificationService()->notify($userId, 'student-remove', array(
            'classId' => $classroom['id'], 
            'classroomTitle' => $classroom['title'],
        ));

        return $this->createJsonResponse(true);
    }

    public function createAction(Request $request, $id)
    {
        // $courseSetting = $this->getSettingService()->get('course', array());
        
        // if (!empty($courseSetting['teacher_manage_student'])) {
        //     $course = $this->getCourseService()->tryManageCourse($id);
        // } else {
        //     $course = $this->getCourseService()->tryAdminCourse($id);
        // }

        $classroom=$this->getClassroomService()->getClassroom($id);

        $currentUser = $this->getCurrentUser();

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();
            $user = $this->getUserService()->getUserByNickname($data['nickname']);
            if (empty($user)) {
                throw $this->createNotFoundException("用户{$data['nickname']}不存在");
            }

            if ($this->getClassroomService()->isClassroomStudent($classroom['id'], $user['id'])) {
                throw $this->createNotFoundException("用户已经是学员，不能添加！");
            }

            $order = $this->getOrderService()->createOrder(array(
                'userId' => $user['id'],
                'title' => "购买班级《{$classroom['title']}》(管理员添加)",
                'targetType' => 'classroom',
                'targetId' => $classroom['id'],
                'amount' => $data['price'],
                'payment' => 'none',
                'snPrefix' => 'CR',
            ));

            $this->getOrderService()->payOrder(array(
                'sn' => $order['sn'],
                'status' => 'success', 
                'amount' => $order['amount'], 
                'paidTime' => time(),
            ));

            $info = array(
                'orderId' => $order['id'],
                'note'  => $data['remark'],
            );

            $this->getClassroomService()->becomeStudent($order['targetId'], $order['userId'], $info);

            $member = $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']);

            $this->getNotificationService()->notify($member['userId'], 'student-create', array(
                'classId' => $classroom['id'], 
                'classroomTitle' => $classroom['title'],
            ));

            $this->getLogService()->info('classroom', 'add_student', "班级《{$classroom['title']}》(#{$classroom['id']})，添加学员{$user['nickname']}(#{$user['id']})，备注：{$data['remark']}");

            return $this->createStudentTrResponse($classroom, $member);
        }

        return $this->render('TopxiaWebBundle:ClassroomManage:create-modal.html.twig',array(
            'classroom'=>$classroom,
        ));
    }

    public function checkNicknameAction(Request $request, $id)
    {
        $nickname = $request->query->get('value');
        $result = $this->getUserService()->isNicknameAvaliable($nickname);
        if ($result) {
            $response = array('success' => false, 'message' => '该用户不存在');
        } else {
            $user = $this->getUserService()->getUserByNickname($nickname);
            $isClassroomStudent = $this->getClassroomService()->isClassroomStudent($id, $user['id']);
            if($isClassroomStudent){
                $response = array('success' => false, 'message' => '该用户已是本班级的学员了');
            } else {
                $response = array('success' => true, 'message' => '');
            }
            
            $isClassroomTeacher = $this->getClassroomService()->isClassroomTeacher($id, $user['id']);
            if($isClassroomTeacher){
                $response = array('success' => false, 'message' => '该用户是本班级的教师，不能添加');
            }
        }
        return $this->createJsonResponse($response);
    }

    public function teachersAction(Request $request,$id)
    {   
        $classroom=$this->getClassroomService()->getClassroom($id);

        $fields=array();

        if($request->getMethod()=="POST"){

            $data=$request->request->all();

            if(isset($data['teacherIds'])){
                $teacherIds=$data['teacherIds'];
                $teacherIds=json_encode($teacherIds);

                $fields=array('teacherIds'=>$teacherIds);  
            }

            if(isset($data['headerTeacherId']))
            {
                $fields['headerTeacherId']=$data['headerTeacherId'];
            }

            if($fields)
            $classroom=$this->getClassroomService()->updateClassroom($id,$fields);

            $this->setFlashMessage('success',"保存成功！");
        }

        $teacherIds=$classroom['teacherIds'];

        $teachers=$this->getUserService()->findUsersByIds($teacherIds);

        $headerTeacher=$this->getUserService()->getUser($classroom['headerTeacherId']);

        return $this->render("TopxiaWebBundle:ClassroomManage:teachers.html.twig",array(
            'classroom'=>$classroom,
            'teachers'=>$teachers,
            'teacherIds'=>$teacherIds,
            'headerTeacher'=>$headerTeacher));
    }

    public function setInfoAction(Request $request,$id)
    {   
        $classroom=$this->getClassroomService()->getClassroom($id);

        if($request->getMethod()=="POST"){

            $class=$request->request->all();

            $this->setFlashMessage('success',"基本信息设置成功！");

            $classroom=$this->getClassroomService()->updateClassroom($id,$class);
        }

        return $this->render("TopxiaWebBundle:ClassroomManage:set-info.html.twig",array(
            'classroom'=>$classroom));
    }

    public function setAction(Request $request,$id)
    {   
        $classroom=$this->getClassroomService()->getClassroom($id);

        if ($this->setting('vip.enabled')) {
            $levels = $this->getLevelService()->findEnabledLevels();
        } else {
            $levels = array();
        }

        if($request->getMethod()=="POST"){

            $class=$request->request->all();

            if($class['vipLevelId']=="") $class['vipLevelId']=0;

            $this->setFlashMessage('success',"设置成功！");

            $classroom=$this->getClassroomService()->updateClassroom($id,$class);
        }

        return $this->render("TopxiaWebBundle:ClassroomManage:set.html.twig",array(
            'levels' => $this->makeLevelChoices($levels),
            'classroom'=>$classroom));
    }

    public function setPictureAction(Request $request,$id)
    {   
        $classroom=$this->getClassroomService()->getClassroom($id);

        if($request->getMethod()=="POST"){

            $file = $request->files->get('picture');
            if (!FileToolkit::isImageFile($file)) {
                return $this->createMessageResponse('error', '上传图片格式错误，请上传jpg, gif, png格式的文件。');
            }

            $filenamePrefix = "classroom_{$classroom['id']}_";
            $hash = substr(md5($filenamePrefix . time()), -8);
            $ext = $file->getClientOriginalExtension();
            $filename = $filenamePrefix . $hash . '.' . $ext;

            $directory = $this->container->getParameter('topxia.upload.public_directory') . '/tmp';
            $file = $file->move($directory, $filename);

            $fileName = str_replace('.', '!', $file->getFilename());

            return $this->redirect($this->generateUrl('classroom_manage_picture_crop', array(
                'id' => $classroom['id'],
                'file' => $fileName)
            ));
        }

        return $this->render("TopxiaWebBundle:ClassroomManage:set-picture.html.twig",array(
            'classroom'=>$classroom));
    }

    public function pictureCropAction(Request $request, $id)
    {
        $classroom=$this->getClassroomService()->getClassroom($id);
      
        //@todo 文件名的过滤
        $filename = $request->query->get('file');
        $filename = str_replace('!', '.', $filename);
        $filename = str_replace(array('..' , '/', '\\'), '', $filename);

        $pictureFilePath = $this->container->getParameter('topxia.upload.public_directory') . '/tmp/' . $filename;

        if($request->getMethod() == 'POST') {
            $c = $request->request->all();
            $this->getClassroomService()->changePicture($classroom['id'], $pictureFilePath, $c);
            return $this->redirect($this->generateUrl('classroom_manage_set_picture', array('id' => $classroom['id'])));
        }

        try {
        $imagine = new Imagine();
            $image = $imagine->open($pictureFilePath);
        } catch (\Exception $e) {
            @unlink($pictureFilePath);
            return $this->createMessageResponse('error', '该文件为非图片格式文件，请重新上传。');
        }

        $naturalSize = $image->getSize();
        $scaledSize = $naturalSize->widen(480)->heighten(270);

        $assets = $this->container->get('templating.helper.assets');
        $pictureUrl = $this->container->getParameter('topxia.upload.public_url_path') . '/tmp/' . $filename;
        $pictureUrl = ltrim($pictureUrl, ' /');
        $pictureUrl = $assets->getUrl($pictureUrl);

        return $this->render('TopxiaWebBundle:ClassroomManage:picture-crop.html.twig', array(
            'classroom' => $classroom,
            'pictureUrl' => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
        ));
    }
    
    public function coursesAction(Request $request,$id)
    {   
        $userIds = array();
        $coinPrice=0;
        $price=0;

        $classroom=$this->getClassroomService()->getClassroom($id);

        if($request->getMethod() == 'POST') {

            $courseIds=$request->request->get('courseIds');
            
            if(empty($courseIds)) $courseIds=array();

            $this->getClassroomService()->updateCourses($id,$courseIds);

            $this->getClassroomService()->updateClassroomTeachers($id);

            $this->setFlashMessage('success',"课程修改成功");
        }

        $classroomCourses=$this->getClassroomService()->getAllCourses($id);
        $courseIds=ArrayToolkit::column($classroomCourses,'courseId');

        $courses=$this->getCourseService()->findCoursesByIds($courseIds);

        foreach ($courses as $course) {
            $userIds = array_merge($userIds, $course['teacherIds']);

            $coinPrice+=$course['coinPrice'];
            $price+=$course['price'];
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render("TopxiaWebBundle:ClassroomManage:courses.html.twig",array(
            'classroom'=>$classroom,
            'classroomCourses'=>$classroomCourses,
            'courses'=>$courses,
            'price'=>$price,
            'coinPrice'=>$coinPrice,
            'users'=>$users));
    }


    public function coursesSelectAction(Request $request,$id)
    {
        $data=$request->request->all();

        $ids=$data['ids'];

        $ids=explode(",", $ids);

        foreach ($ids as $key => $value) {
            
            $course=$this->getClassroomService()->getCourseByClassroomIdAndCourseId($id,$value);

            if(empty($course))
            $this->getClassroomService()->addCourse($id,$value);
        
        }

        $this->getClassroomService()->updateClassroomTeachers($id);

        $this->setFlashMessage('success',"课程添加成功");

        return new Response('success');
    }

    public function publishAction($id)
    {
        $classroom=$this->getClassroomService()->getClassroom($id);

        $this->getClassroomService()->publishClassroom($id);

        return new Response("success");
    }

    public function checkNameAction(Request $request)
    {   
        $nickName=$request->request->get('name');
        $user=array();

        if($nickName!=""){

            $user = $this->getUserService()->searchUsers(array('nickname'=>$nickName, 'roles'=> 'ROLE_TEACHER'), array('createdTime', 'DESC'), 0, 1);

        }

        $user=$user ? $user[0] :array();
   
        return $this->render('TopxiaWebBundle:ClassroomManage:teacher-info.html.twig',array(
            'user'=>$user));
    }

    public function closeAction($id)
    {
        $classroom=$this->getClassroomService()->getClassroom($id);

        $this->getClassroomService()->closeClassroom($id);

        return new Response("success");
    }

    private function makeLevelChoices($levels)
    {
        $choices = array();
        foreach ($levels as $level) {
            $choices[$level['id']] = $level['name'];
        }
        return $choices;
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom.ClassroomService');
    }

    protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    private function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

}
