<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\FileToolkit;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Twig\Extension\DataDict;

class SchoolController extends BaseController
{
    public function schoolSettingAction(Request $request) 
    {
        if ($request->getMethod() == 'POST') {
            $school = $request->request->all();
            $this->getSettingService()->set('school', $school);
            $this->getLogService()->info('school', 'update_settings', "更新学校设置", $school);
            $this->setFlashMessage('success', '学校信息设置已保存！');
        }

        $school = $this->getSettingService()->get('school', array());

        $default = array(
            'primarySchool' => 0,
            'primaryYear' => 6,
            'middleSchool' => 0,
            'highSchool' => 0,
            'homepagePicture' => '',
        );

        $school = array_merge($default, $school);

      
        return $this->render('TopxiaAdminBundle:School:school-setting.html.twig', array(
            'school' => $school
        ));
    }

    public function classSettingAction(Request $request) 
    {
        $conditions = $request->query->all();
            
        $paginator = new Paginator(
            $this->get('request'),
            $this->getClassesService()->searchClassCount($conditions),
            10);

        $classes = $this->getClassesService()->searchClasses(
            $conditions,
            array(),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($classes, 'headTeacherId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $users = ArrayToolkit::index($users, 'id');
        foreach ($classes as $key => $class) {
            $headTeacher = $this->getUserService()->getUser($class['headTeacherId']);
            $class['headTeacherName'] = $users[$class['headTeacherId']]['truename'];
            $classes[$key] = $class;
        }  
        return $this->render('TopxiaAdminBundle:School:class-setting.html.twig',array(
            'classes' => $classes,
            'paginator' => $paginator,
        ));
    }

    public function pointSettingAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $point = $request->request->all();
            $this->getSettingService()->set('point', $point);
            $this->getLogService()->info('point', 'update_settings', "更新学分设置", $point);
            $this->setFlashMessage('success', '学分设置已保存！');
        }

        $point = $this->getSettingService()->get('point', array());

        $default = array(
            'accomplishLesson' => 2,
            'shareNote' => 3,
            'noteByLiked' => 2,
            'accomplishTest' => 3,
            'accomplishHomework' => 3,
            'accomplishPractice' => 3,
            'accomplishSign' => 1,
        );

        $point = array_merge($default, $point);

      
        return $this->render('TopxiaAdminBundle:School:point-setting.html.twig', array(
            'point' => $point
        ));
    }

    public function classEditorAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $classId = $fields['classId'];
            unset($fields['classId']);
            if($classId) {
                $class = $this->getClassesService()->editClass($fields,$classId);
                return new Response('success');
            } else {
                $class = $this->getClassesService()->createClass($fields);
                return new Response('sucess');
            }
        }

        $type = $request->query->get('type');
        if($type == 'create') {
            return $this->render('TopxiaAdminBundle:School:class-editor.html.twig',array());
        } else {
            $classId = $request->query->get('classId');
            $class = $this->getClassesService()->getClass($classId);
            $headTheacher = $this->getUserService()->getUser($class['headTeacherId']);
            $class['headTeacherName'] = $headTheacher['truename'];
            return $this->render('TopxiaAdminBundle:School:class-editor.html.twig',array(
                'class' => $class,
            ));
        }

        
    }

    public function classDeleteAction(Request $request, $classId)
    {
        $this->getClassesService()->deleteClass($classId);
        return $this->redirect($this->generateUrl('admin_school_classes_setting'));
    }

    public function classCourseManageAction(Request $request, $classId)
    {
        $conditions =array(
            'classId' => $classId,
            'status' => 'published'
        );

        $class = $this->getClassesService()->getClass($classId);
        
        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            'latest',
            0,
            1000
        );
        foreach ($courses as $key => $course) {
            foreach ($course['teacherIds'] as $key2 => $id) {

                $headTeacher = $this->getUserService()->getUser($id);
                $course['teachername'][$key2] = $headTeacher['truename'];

            }
            $courses[$key] = $course;
        }

        return $this->render('TopxiaAdminBundle:School:class-course-manage.html.twig',array(
            'courses' => $courses,
            'class' => $class,
        ));
    }

    public function classCourseAddAction(Request $request, $classId)
    {
        if($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $hasCourse = $this->getCourseService()->classHasCourse($classId, $fields['parentId']);
            if ($hasCourse) {
                throw $this->createNotFoundException("已经添加该课程");
            } else {
                $this->getCourseService()->copyCourseForClass(
                    $fields['parentId'],
                    $classId,
                    $fields['compulsory'],
                    $fields['teacherId']);
            }

            return new Response(json_encode("success"));
        }

        $params = $request->query->all();
        $params['classId'] = $classId;
        $classCourses = $this->getCourseService()->findCoursesByClassId($classId);
        if (empty($classCourses)) {
            $classCourses=array();
        }
        $excludeIds = ArrayToolkit::column($classCourses, 'parentId');
        $conditions = array(
            'status' => 'published',
            'parentId' => 0,
            'excludeIds' => $excludeIds
        );
        if($params['public'] == '1') {
            $conditions['gradeId'] = 0;
        }else{
            $conditions['gradeId'] = $params['gradeId'];
        }
        

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchCourseCount($conditions),
            5);

        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($courses, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $users = ArrayToolkit::index($users, 'id');
        foreach ($courses as $key => $course) {
            $creator = $users[$course['userId']];
            $course['creatorName'] = $creator['truename'];
            $courses[$key] = $course;
        }

        return $this->render('TopxiaAdminBundle:School:class-course-add-modal.html.twig',array(
            'class' => $params,
            'courses' => $courses,
            'paginator' => $paginator,            
        ));
    }

    public function classCourseRemoveAction(Request $request,$classId,$courseId)
    {

        $this->getCourseService()->closeCourse($courseId);
        return $this->redirect($this->generateUrl('admin_school_class_course_manage',array('classId'=>$classId)));
    }

    public function homePageUploadAction(Request $request)
    {
        $school = $this->getSettingService()->get('school', array());
        $newFileName='school-homepage'.time();
        $fileLocation = $this->savePicture($request, 'homepagePicture', 'school', $newFileName);
        
        $school['homepagePicture'] = $fileLocation['path'];

        $this->getSettingService()->set('school', $school);
        $this->getLogService()->info('school', 'update_settings', "更新学校首页图片", array('homepagePicture' => $school['homepagePicture']));

        $response = array(
            'path' => $fileLocation['path'],
            'url' =>  $this->container->get('templating.helper.assets')->getUrl($fileLocation['url']),
        );

        return new Response(json_encode($response));
    }

    public function homePageRemoveAction(Request $request)
    {
        $setting = $this->getSettingService()->get("school");
        $setting['homepagePicture'] = '';

        $this->getSettingService()->set('school', $setting);

        $this->getLogService()->info('school', 'update_settings', "移除移除学校首页图片");

        return $this->createJsonResponse(true);
    }

    public function classIconUploadAction(Request $request)
    {
        $fileLocation = $this->savePicture($request, 'icon', 'school/class/icon', '');

        $response = array(
            'path' => $fileLocation['path'],
            'url' =>  $this->container->get('templating.helper.assets')->getUrl($fileLocation['url']),
        );

        return new Response(json_encode($response));
    }

    public function classBackgroundImgUploadAction(Request $request)
    {
        $fileLocation = $this->savePicture($request, 'backgroundImg', 'school/class/backgroundImg', '');
        
        $response = array(
            'path' => $fileLocation['path'],
            'url' =>  $this->container->get('templating.helper.assets')->getUrl($fileLocation['url']),
        );

        return new Response(json_encode($response));
    }
    
    public function teacherNameAction()
    {
        $conditions = array(
            'roles' => 'ROLE_TEACHER' 
            );
        $total = $this->getUserService()->searchUserCount($conditions);
        $teachers = $this->getUserService()->searchUsers(
            $conditions,
            array('id','ASC'),
            0,
            $total);

        $response = array();
        foreach ($teachers as $key => $teacher) {
            $temp = array();
            $temp['id'] = $teacher['id'];
            $temp['name'] = $teacher['truename'];
            $response[] = $temp;
        }
        return new Response(json_encode($response)); 
    }

    public function studentListAction (Request $request,$classId)
    {   
        $class=$this->getClassesService()->getClass($classId);
        $conditions = array(
            'classId'=>$classId,
            'roles'=>array('STUDENT')
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getClassesService()->searchClassMemberCount($conditions),
            20
        );
        $classMembers = $this->getClassesService()->searchClassMembers(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($classMembers, 'userId'));
        return $this->render('TopxiaAdminBundle:School:student-list.html.twig', array(
            'users' => $users ,
            'class'=>$class,
            'paginator' => $paginator
        ));
    }

    public function studentImportAction(Request $request, $classId)
    {
        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();
            $formData['numbers'] = str_replace('，', ',', $formData['numbers']); 
            $formData['numbers'] = str_replace("\n", ',', $formData['numbers']); 
            $formData['numbers'] = str_replace("\r", ',', $formData['numbers']); 
            $formData['numbers'] = str_replace(' ', ',', $formData['numbers']); 
            $numbers = explode(',', $formData['numbers']);
            $userIds=array();
            foreach ($numbers as $number) {
                $number=trim($number);
                if($number==''){
                    continue;
                }
                $user=$this->getUserService()->getUserByNumber($number);
                if(empty($user) || in_array('ROLE_TEACHER', $user['roles'])){
                    return $this->createJsonResponse('学号'.$number.'的学生，账号还未建立！');
                }
                $studentMember=$this->getClassesService()->getStudentMemberByUserIdAndClassId($user['id'],null);
                
                if(!empty($studentMember) && $studentMember['classId']!=$classId){
                    $existClass = $this->getClassesService()->getClass($studentMember['classId']);
                    $gradeName = DataDict::text('gradeName', $existClass['gradeId']);
                    return $this->createJsonResponse($user['truename'].'('.'学号'.$number.')'.'已被分配到'.$gradeName.$existClass['name'].'，请先到该班级中把他移除！');
                }
                if(!empty($studentMember) && $studentMember['classId']==$classId){
                    continue;
                }

                $userIds[]=$user['id'];
            }
            $this->getClassesService()->importStudents($classId,$userIds);
            return $this->createJsonResponse(true);
        }
        return $this->render('TopxiaAdminBundle:School:student-import-modal.html.twig', array(
            'classId' => $classId
        ));
    }
    
    public function studentRemoveAction(Request $request, $userId ,$classId)
    {
        $this->getClassesService()->deleteClassMemberByUserId($userId);
        $this->getClassesService()->updateClassStudentNum(-1,$classId);
        return $this->createJsonResponse(true);
    }

    private function savePicture(Request $request, $uploadFileName, $folder, $newFileName = '')
    {
        $result = array();
        $file = $request->files->get($uploadFileName);
        if (!FileToolkit::isImageFile($file)) {
            throw $this->createAccessDeniedException('图片格式不正确！');
        }
        if(!$newFileName) {
            $newFileName = time() . '.' . $file->getClientOriginalExtension();
        } else {
            $newFileName = $newFileName . '.' . $file->getClientOriginalExtension();
        }
            
        $directory = "{$this->container->getParameter('topxia.upload.public_directory')}/" . $folder;
        $file = $file->move($directory, $newFileName);

        $result['path'] = $folder . "/{$newFileName}";
        $result['url'] = $this->container->getParameter('topxia.upload.public_url_path') .  '/' . $result['path'];
        return $result;
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getClassesService()
    {
        return $this->getServiceKernel()->createService('Classes.ClassesService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}