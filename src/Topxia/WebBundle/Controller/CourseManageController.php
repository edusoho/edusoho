<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\WebBundle\Form\ReviewType;
use Topxia\Service\Course\CourseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;

class CourseManageController extends BaseController
{
	public function indexAction(Request $request, $id)
	{
        return $this->forward('TopxiaWebBundle:CourseManage:base',  array('id' => $id));
	}

	public function baseAction(Request $request, $id)
	{
		$course = $this->getCourseService()->getCourse($id);
		$form = $this->createCourseBaseForm($course);
	    if($request->getMethod() == 'POST'){
	        $form->bind($request);
	        if($form->isValid()){
	            $courseBaseInfo = $form->getData();
	            $this->getCourseService()->updateCourse($id, $courseBaseInfo);
	            $this->setFlashMessage('success', '课程基本信息已保存！');
	            return $this->redirect($this->generateUrl('course_manage_base',array('id' => $id))); 
	        }
        }
		return $this->render('TopxiaWebBundle:CourseManage:base.html.twig', array(
			'course' => $course,
			'form' => $form->createView(),
		));
	}

	public function detailAction(Request $request, $id)
	{
        
		$course = $this->getCourseService()->getCourse($id);

	    if($request->getMethod() == 'POST'){
            $detail = $request->request->all();
            $detail['goals'] = (empty($detail['goals']) or !is_array($detail['goals'])) ? array() : $detail['goals'];
            $detail['audiences'] = (empty($detail['audiences']) or !is_array($detail['audiences'])) ? array() : $detail['audiences'];

            $this->getCourseService()->updateCourse($id, $detail);
            $this->setFlashMessage('success', '课程详细信息已保存！');

            return $this->redirect($this->generateUrl('course_manage_detail',array('id' => $id))); 
        }

		return $this->render('TopxiaWebBundle:CourseManage:detail.html.twig', array(
			'course' => $course
		));
	}

	public function pictureAction(Request $request, $id)
	{
		$course = $this->getCourseService()->tryManageCourse($id);

        if($request->getMethod() == 'POST'){
            $file = $request->files->get('picture');

            $filenamePrefix = "course_{$course['id']}_";
            $hash = substr(md5($filenamePrefix . time()), -8);
            $filename = $filenamePrefix . $hash . '.' . $file->getClientOriginalExtension();

            $directory = $this->container->getParameter('topxia.upload.public_directory') . '/tmp';

            $file = $file->move($directory, $filename);

            return $this->redirect($this->generateUrl('course_manage_picture_crop', array(
                'id' => $course['id'],
                'file' => $file->getFilename())
            ));
        }

		return $this->render('TopxiaWebBundle:CourseManage:picture.html.twig', array(
			'course' => $course,
		));
	}

    public function pictureCropAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        //@todo 文件名的过滤
        $filename = $request->query->get('file');
        $filename = str_replace(array('..' , '/', '\\'), '', $filename);

        $pictureFilePath = $this->container->getParameter('topxia.upload.public_directory') . '/tmp/' . $filename;

        if($request->getMethod() == 'POST') {
            $c = $request->request->all();
            $this->getCourseService()->changeCoursePicture($course['id'], $pictureFilePath, $c);
            return $this->redirect($this->generateUrl('course_manage_picture', array('id' => $course['id'])));
        }

        $imagine = new Imagine();
        $image = $imagine->open($pictureFilePath);

        $naturalSize = $image->getSize();
        $scaledSize = $naturalSize->widen(480)->heighten(270);
        $pictureUrl = $this->container->getParameter('topxia.upload.public_url_path') . '/tmp/' . $filename;

        return $this->render('TopxiaWebBundle:CourseManage:picture-crop.html.twig', array(
            'course' => $course,
            'pictureUrl' => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
        ));
    }

    public function priceAction(Request $request, $id)
    {

        if ($request->getMethod() == 'POST') {
            $this->getCourseService()->updateCourse($id, $request->request->all());
            $this->setFlashMessage('success', '课程价格已经修改成功!');
        }

        $course = $this->getCourseService()->getCourse($id);
        return $this->render('TopxiaWebBundle:CourseManage:price.html.twig', array(
            'course' => $course
        ));
        
    }

    public function teachersAction(Request $request, $id)
    {
        if($request->getMethod() == 'POST'){
        	
            $data = $request->request->all();
            $data['ids'] = empty($data['ids']) ? array() : array_values($data['ids']);

            $teachers = array();
            foreach ($data['ids'] as $teacherId) {
            	$teachers[] = array(
            		'id' => $teacherId,
            		'isVisible' => empty($data['visible_' . $teacherId]) ? 0 : 1
        		);
            }
            $this->getCourseService()->setCourseTeachers($id, $teachers);
            $this->setFlashMessage('success', '教师设置成功！');

            return $this->redirect($this->generateUrl('course_manage_teachers',array('id' => $id))); 
        }

        $teacherMembers = $this->getCourseService()->findCourseTeachers($id);
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($teacherMembers, 'userId'));

        $teachers = array();
        foreach ($teacherMembers as $member) {
        	if (empty($users[$member['userId']])) {
        		continue;
        	}
        	$teachers[] = array(
                'id' => $member['userId'],
        		'nickname' => $users[$member['userId']]['nickname'],
                'avatar'  => $this->getWebExtension()->getFilePath($users[$member['userId']]['smallAvatar'], 'avatar.png'),
        		'isVisible' => $member['isVisible'] ? true : false,
    		);
        }
        
        return $this->render('TopxiaWebBundle:CourseManage:teachers.html.twig', array(
            'course' => $this->getCourseService()->getCourse($id),
            'teachers' => $teachers
        ));
    }

    public function publishAction(Request $request, $id)
    {
    	$this->getCourseService()->publishCourse($id);
    	return $this->createJsonResponse(true);
    }

    public function teachersMatchAction(Request $request)
    {
        $likeString = $request->query->get('q');
        $users = $this->getUserService()->searchUsers(array('nicknameLike'=>$likeString, 'roles'=> 'ROLE_TEACHER'), array('createdTime', 'DESC'), 0, 10);

        $teachers = array();
        foreach ($users as $user) {
            $teachers[] = array(
                'id' => $user['id'],
                'nickname' => $user['nickname'],
                'avatar' => $this->getWebExtension()->getFilePath($user['smallAvatar'], 'avatar.png'),
                'isVisible' => 1,
            );
        }

        return $this->createJsonResponse($teachers);
    }

	private function createCourseBaseForm($course)
	{
		$builder = $this->createNamedFormBuilder('course', $course)
			->add('title', 'text')
			->add('subtitle', 'textarea')
			->add('tags', 'tags')
			->add('categoryId', 'default_category', array(
				'empty_value' => '请选择分类'
			)
        );

	    return $builder->getForm();
	}

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }

    private function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
    }

}