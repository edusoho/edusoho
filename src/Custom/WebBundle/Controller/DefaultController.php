<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Topxia\System;

use Topxia\Service\Util\CloudClientFactory;

class DefaultController extends BaseController
{
	public function indexAction()
	{

	}

	public function starTeacherAction()
	{
		$conditions = array(
            'roles'=>'ROLE_TEACHER',
            'promoted'=>'1',
        );

		$teachers = $this->getUserService()->searchUsers(
			$conditions,
			array('promotedTime', 'DESC'),
			0,
			1
		);

		$profiles = $this->getUserService()->findUserProfilesByIds(ArrayToolkit::column($teachers, 'id'));

		$teacherCourseCounts = array();
		foreach ($teachers as $teacher) {
			$teacherCourseCounts[$teacher['id']] = $this->getCourseService()->findUserTeachCourseCount($teacher['id'], true);
		}

		return $this->render('TopxiaWebBundle:Default:starTeacher.html.twig', array(
			'teachers' => $teachers,
			'profiles' => $profiles,
			'teacherCourseCounts' => $teacherCourseCounts
		));

	}


    public function recommendTeachersAction($count)
    {
        $conditions = array(
            'roles'=>'ROLE_TEACHER',
            'promoted'=>'1',
        );

        $teachers = $this->getUserService()->searchUsers(
            $conditions,
            array('promotedTime', 'DESC'),
            0,
            $count
        );

        $profiles = $this->getUserService()->findUserProfilesByIds(ArrayToolkit::column($teachers, 'id'));

        $teacherCourseCounts = array();
        foreach ($teachers as $teacher) {
            $teacherCourseCounts[$teacher['id']] = $this->getCourseService()->findUserTeachCourseCount($teacher['id'], true);
        }

        return $this->render('TopxiaWebBundle:Default:teachers.html.twig', array(
            'teachers' => $teachers,
            'profiles' => $profiles,
            'teacherCourseCounts' => $teacherCourseCounts
        ));

    }

	public function homeSwfAction(Request $request)
	{
		$course = $this->getCourseService()->getCourse(17);
        $lesson = $this->getCourseService()->getCourseLesson(17, 4);
        if (empty($lesson)) {
            throw $this->createNotFoundException();
        }

        if ($lesson['type'] == 'video' and $lesson['mediaSource'] == 'self') {
            $file = $this->getUploadFileService()->getFile($lesson['mediaId']);
            if (!empty($file['metas2']) && !empty($file['metas2']['hd']['key'])) {
                $factory = new CloudClientFactory();
                $client = $factory->createClient();
                $hls = $client->generateHLSQualitiyListUrl($file['metas2'], 3600);
            }
        }

        return $this->render('TopxiaWebBundle:Default:preview-modal.html.twig', array(
            'course' => $course,
            'lesson' => $lesson,
            'hlsUrl' => (isset($hls) and is_array($hls) and !empty($hls['url'])) ? $hls['url'] : '',
        ));



	}

	protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

     private function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }

    private function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }


}