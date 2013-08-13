<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\WebBundle\Form\ReviewType;
use Topxia\Service\Course\CourseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

use Imagine\Imagick\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;



class CourseManageController extends BaseController
{
	public function indexAction(Request $request, $id)
	{
		$course = $this->getCourseService()->getCourse($id);
		return $this->render('TopxiaWebBundle:CourseManage:index.html.twig', array(
			'course' => $course
		));
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
		$course = $this->getCourseService()->getCourse($id);
        $form = $this->createFormBuilder()
            ->add('picture', 'file')
            ->getForm();
        if($request->getMethod() == 'POST'){
            $form->bind($request);
            if ($form->isValid()) {
                $fields = $form->getData();
                $this->getCourseService()->changeCoursePicture($course['id'], $fields['picture']);
	            $this->setFlashMessage('success', '课程图片已上传成功！');
	            return $this->redirect($this->generateUrl('course_manage_picture',array('id' => $id))); 
            } else {
                return $this->createJsonResponse(false);
            }
        }
		return $this->render('TopxiaWebBundle:CourseManage:picture.html.twig', array(
			'course' => $course,
			'form' => $form->createView()
		));
	}

    public function cropPictureAction(Request $request, $id)
    {
        $course = $this->getCourseService()->getCourse($id);
        if($request->getMethod() == 'POST'){
            $x = (int)$request->request->get('x');
            $y = (int)$request->request->get('y');
            $w = (int)$request->request->get('w');
            $h = (int)$request->request->get('h');
            if(($w <= 0) || ($h <= 0)){
                throw new \RuntimeException('裁剪的参数大小有问题，请重新裁剪！');
            }
            $imagine = new Imagine();
            $uri = $this->getFileService()->parseFileUri($course['largePicture']);
            $realpath = 'files/'.$uri['path'];
            $result = $imagine->open($realpath)->crop(new Point($x, $y), new Box($w, $h))
                ->save($realpath);
            if(!empty($result)){
                return $this->redirect($this->generateUrl('course_manage_picture', array('id' => $course['id'])));
            }
        }
        return $this->render('TopxiaWebBundle:CourseManage:crop.html.twig', array(
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
        $users = $this->getUserService()->searchUsers(array('nicknameLike'=>$likeString), 0, 10);

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
			))
			->add('price', 'number', array(
	            'precision' => 2,
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