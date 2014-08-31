<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\WebBundle\Form\ReviewType;
use Topxia\Service\Course\CourseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\Common\FileToolkit;
use Topxia\Service\Util\LiveClientFactory;

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
		$course = $this->getCourseService()->tryManageCourse($id);
        $courseSetting = $this->getSettingService()->get('course', array());

	    if($request->getMethod() == 'POST'){
            $data = $request->request->all();

            $this->getCourseService()->updateCourse($id, $data);
            $this->setFlashMessage('success', '课程基本信息已保存！');
            return $this->redirect($this->generateUrl('course_manage_base',array('id' => $id))); 
        }

        $tags = $this->getTagService()->findTagsByIds($course['tags']);

        if ($course['type'] == 'live') {
            $client = LiveClientFactory::createClient();
            $liveCapacity = $client->getCapacity();
        } else {
            $liveCapacity = null;
        }

		return $this->render('TopxiaWebBundle:CourseManage:base.html.twig', array(
			'course' => $course,
            'tags' => ArrayToolkit::column($tags, 'name'),
            'liveCapacity' => empty($liveCapacity['capacity']) ? 0 : $liveCapacity['capacity'],
		));
	}

    public function nicknameCheckAction(Request $request, $courseId)
    {
        $nickname = $request->query->get('value');
        $result = $this->getUserService()->isNicknameAvaliable($nickname);
        if ($result) {
            $response = array('success' => false, 'message' => '该用户还不存在！');
        } else {
            $user = $this->getUserService()->getUserByNickname($nickname);
            $isCourseStudent = $this->getCourseService()->isCourseStudent($courseId, $user['id']);
            if($isCourseStudent){
                $response = array('success' => false, 'message' => '该用户已是本课程的学员了！');
            } else {
                $response = array('success' => true, 'message' => '');
            }
            
            $isCourseTeacher = $this->getCourseService()->isCourseTeacher($courseId, $user['id']);
            if($isCourseTeacher){
                $response = array('success' => false, 'message' => '该用户是本课程的教师，不能添加!');
            }
        }
        return $this->createJsonResponse($response);
    }

	public function detailAction(Request $request, $id)
	{
        
		$course = $this->getCourseService()->tryManageCourse($id);

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
            if (!FileToolkit::isImageFile($file)) {
                return $this->createMessageResponse('error', '上传图片格式错误，请上传jpg, gif, png格式的文件。');
            }

            $filenamePrefix = "course_{$course['id']}_";
            $hash = substr(md5($filenamePrefix . time()), -8);
            $ext = $file->getClientOriginalExtension();
            $filename = $filenamePrefix . $hash . '.' . $ext;

            $directory = $this->container->getParameter('topxia.upload.public_directory') . '/tmp';
            $file = $file->move($directory, $filename);

            $fileName = str_replace('.', '!', $file->getFilename());

            return $this->redirect($this->generateUrl('course_manage_picture_crop', array(
                'id' => $course['id'],
                'file' => $fileName)
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
        $filename = str_replace('!', '.', $filename);
        $filename = str_replace(array('..' , '/', '\\'), '', $filename);

        $pictureFilePath = $this->container->getParameter('topxia.upload.public_directory') . '/tmp/' . $filename;

        if($request->getMethod() == 'POST') {
            $c = $request->request->all();
            $this->getCourseService()->changeCoursePicture($course['id'], $pictureFilePath, $c);
            return $this->redirect($this->generateUrl('course_manage_picture', array('id' => $course['id'])));
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

        // @todo fix it.
        $assets = $this->container->get('templating.helper.assets');
        $pictureUrl = $this->container->getParameter('topxia.upload.public_url_path') . '/tmp/' . $filename;
        $pictureUrl = ltrim($pictureUrl, ' /');
        $pictureUrl = $assets->getUrl($pictureUrl);

        return $this->render('TopxiaWebBundle:CourseManage:picture-crop.html.twig', array(
            'course' => $course,
            'pictureUrl' => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
        ));
    }

    public function priceAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $canModifyPrice = true;
        $teacherModifyPrice = $this->setting('course.teacher_modify_price', true);
        if ($this->setting('vip.enabled')) {
            $levels = $this->getLevelService()->findEnabledLevels();
        } else {
            $levels = array();
        }
        if (empty($teacherModifyPrice)) {
            if (!$this->getCurrentUser()->isAdmin()) {
                $canModifyPrice = false;
                goto response;
            }
        }

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            if(isset($fields['freeStartTime'])){
                $fields['freeStartTime'] = strtotime($fields['freeStartTime']);
                $fields['freeEndTime'] = strtotime($fields['freeEndTime']);
            }
            
            $course = $this->getCourseService()->updateCourse($id, $fields);
            $this->setFlashMessage('success', '课程价格已经修改成功!');
        }



        response:
        return $this->render('TopxiaWebBundle:CourseManage:price.html.twig', array(
            'course' => $course,
            'canModifyPrice' => $canModifyPrice,
            'levels' => $this->makeLevelChoices($levels),
        ));
    }

    private function makeLevelChoices($levels)
    {
        $choices = array();
        foreach ($levels as $level) {
            $choices[$level['id']] = $level['name'];
        }
        return $choices;
    }

    public function teachersAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

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
            'course' => $course,
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
        $users = $this->getUserService()->searchUsers(array('nickname'=>$likeString, 'roles'=> 'ROLE_TEACHER'), array('createdTime', 'DESC'), 0, 10);

        $teachers = array();
        foreach ($users as $user) {
            $teachers[] = array(
                'id' => $user['id'],
                'nickname' => $user['nickname'],
                'avatar' => $this->getWebExtension()->getFilePath($user['smallAvatar'], 'avatar.png'),
                'isVisible' => 1
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
            ->add('expiryDay', 'text')
			->add('categoryId', 'default_category', array(
				'empty_value' => '请选择分类'
			));

	    return $builder->getForm();
	}

    private function calculateUserLearnProgress($course, $member)
    {
        if ($course['lessonNum'] == 0) {
            return array('percent' => '0%', 'number' => 0, 'total' => 0);
        }

        $percent = intval($member['learnedNum'] / $course['lessonNum'] * 100) . '%';

        return array (
            'percent' => $percent,
            'number' => $member['learnedNum'],
            'total' => $course['lessonNum']
        );
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }

    private function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }

    private function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
    }

    private function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    private function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

    private function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}