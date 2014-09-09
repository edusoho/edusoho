<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\FileToolkit;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

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
            5);

        $classes = $this->getClassesService()->searchClasses(
            $conditions,
            array(),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        foreach ($classes as $key => $class) {
            $headTeacher = $this->getUserService()->getUser($class['headTeacherId']);
            $class['headTeacherName'] = $headTeacher['truename'];
            $classes[$key] = $class;
        }  
        return $this->render('TopxiaAdminBundle:School:class-setting.html.twig',array(
            'classes' => $classes,
            'paginator' => $paginator,
        ));
    }

    public function classCreateAction(Request $request)
    {

        if ($request->getMethod() == 'POST') {
            $class = $request->request->all();
            $class = $this->getClassesService()->createClass($class);
            return new Response(json_encode($class));
        }

        return $this->render('TopxiaAdminBundle:School:class-create.html.twig',array(
          
            ));
    }

    public function classEditAction(Request $request, $classId)
    {
        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $class = $this->getClassesService()->editClass($fields,$classId);
            return new Response(json_encode($class));
        }

        $class = $this->getClassesService()->getClass($classId);
        $headTheacher = $this->getUserService()->getUser($class['headTeacherId']);
        $class['headTeacherName'] = $headTheacher['truename'];
        return $this->render('TopxiaAdminBundle:School:class-edit.html.twig',array(
            'class' => $class,
        ));
    }

    public function classDeleteAction(Request $request, $classId)
    {
        $this->getClassesService()->deleteClass($classId);
        return $this->redirect($this->generateUrl('admin_school_classes_setting'));
    }

    public function classCourseManageAction(Request $request, $classId)
    {
        $conditions =array(
            'classId' => $classId
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

    public function classMemberManageAction(Request $request)
    {

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

        $class = $request->query->all();
        $class['classId'] = $classId;
        $classCourses = $this->getCourseService()->findCoursesByClassId($classId);
        if (empty($classCourses)) {
            $classCourses=array();
        }
        $excludeIds = ArrayToolkit::column($classCourses, 'parentId');
        if($class['public'] == '1') {
            $conditions =array(
            'status' => 'published',
            'parentId' => 0,
            'gradeId' => 0,
            'excludeIds' => $excludeIds
        );
        }else{
            $conditions =array(
            'status' => 'published',
            'parentId' => 0,
            'gradeId' => $class['gradeId'],
            'excludeIds' => $excludeIds
            );
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

        foreach ($courses as $key => $course) {
            $creator = $this->getUserService()->getUser($course['userId']);
            $course['creatorName'] = $creator['truename'];
            $courses[$key] = $course;
        }

        return $this->render('TopxiaAdminBundle:School:class-course-add-modal.html.twig',array(
            'class' => $class,
            'courses' => $courses,
            'paginator' => $paginator,            
        ));
    }

    public function classCourseRemoveAction(Request $request,$classId,$courseId)
    {

        $this->getCourseService()->updateCourse($courseId, array('classId'=>0));
        return $this->redirect($this->generateUrl('admin_school_class_course_manage',array('classId'=>$classId)));
    }

    public function homePageUploadAction(Request $request)
    {
        $file = $request->files->get('homepagePicture');
        if (!FileToolkit::isImageFile($file)) {
            throw $this->createAccessDeniedException('图片格式不正确！');
        }

        $filename = 'school-homepage.' . $file->getClientOriginalExtension();
        $directory = "{$this->container->getParameter('topxia.upload.public_directory')}/school";
        $file = $file->move($directory, $filename);

        $school = $this->getSettingService()->get('school', array());


        $path = "school/{$filename}";
        $url = $this->container->getParameter('topxia.upload.public_url_path') .  '/' . $path;

        $school['homepagePicture'] = $path;

        $this->getSettingService()->set('school', $school);
        $this->getLogService()->info('school', 'update_settings', "更新学校首页图片", array('homepagePicture' => $school['homepagePicture']));

        $response = array(
            'path' => $path,
            'url' =>  $this->container->get('templating.helper.assets')->getUrl($url),
        );

        return new Response(json_encode($response));
    }

    public function classIconUploadAction(Request $request)
    {
        $file = $request->files->get('icon');
        if (!FileToolkit::isImageFile($file)) {
            throw $this->createAccessDeniedException('图片格式不正确！');
        }

        $filename = time() . '.' . $file->getClientOriginalExtension();
        
        $directory = "{$this->container->getParameter('topxia.upload.public_directory')}/school/class/icon";
        $file = $file->move($directory, $filename);

        $path = "school/class/icon/{$filename}";
        $url = $this->container->getParameter('topxia.upload.public_url_path') .  '/' . $path;
        
        $response = array(
            'path' => $path,
            'url' =>  $this->container->get('templating.helper.assets')->getUrl($url),
        );

        return new Response(json_encode($response));
    }

    public function classBackgroundImgUploadAction(Request $request)
    {
        $file = $request->files->get('backgroundImg');
        if (!FileToolkit::isImageFile($file)) {
            throw $this->createAccessDeniedException('图片格式不正确！');
        }

        $filename = time() . '.' . $file->getClientOriginalExtension();
        
        $directory = "{$this->container->getParameter('topxia.upload.public_directory')}/school/class/backgroundImg";
        $file = $file->move($directory, $filename);

        $path = "school/class/backgroundImg/{$filename}";
        $url = $this->container->getParameter('topxia.upload.public_url_path') .  '/' . $path;
        $response = array(
            'path' => $path,
            'url' =>  $this->container->get('templating.helper.assets')->getUrl($url),
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
        // $ids = ArrayToolkit::column($teachers,'id');
        // $teacherProfiles = $this->getUserService()->findUserProfilesByIds($ids);
        $response = array();
        foreach ($teachers as $key => $teacher) {
            $temp = array();
            $temp['id'] = $teacher['id'];
            $temp['name'] = $teacher['truename'];
            $response[] = $temp;
        }
        return new Response(json_encode($response)); 
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