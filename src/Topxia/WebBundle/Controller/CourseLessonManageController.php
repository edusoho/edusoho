<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\CloudClientFactory;

class CourseLessonManageController extends BaseController
{
	public function indexAction(Request $request, $id)
	{
		$course = $this->getCourseService()->tryManageCourse($id);
		$courseItems = $this->getCourseService()->getCourseItems($course['id']);

		$mediaMap = array();
		foreach ($courseItems as $item) {
			if ($item['itemType'] != 'lesson') {
				continue;
			}

			if (empty($item['mediaId'])) {
				continue;
			}

			if (empty($mediaMap[$item['mediaId']])) {
				$mediaMap[$item['mediaId']] = array();
			}
			$mediaMap[$item['mediaId']][] = $item['id'];
		}

		$mediaIds = array_keys($mediaMap);

		$files = $this->getUploadFileService()->findFilesByIds($mediaIds);
		foreach ($files as $file) {
			$lessonIds = $mediaMap[$file['id']];
			foreach ($lessonIds as $lessonId) {
				$courseItems["lesson-{$lessonId}"]['mediaStatus'] = $file['convertStatus'];
			}
		}

		return $this->render('TopxiaWebBundle:CourseLessonManage:index.html.twig', array(
			'course' => $course,
			'items' => $courseItems
		));
	}

	// @todo refactor it.
	public function createAction(Request $request, $id)
	{
		$course = $this->getCourseService()->tryManageCourse($id);

	    if($request->getMethod() == 'POST') {
        	$lesson = $request->request->all();
        	$lesson['courseId'] = $course['id'];

        	if ($lesson['media']) {
        		$lesson['media'] = json_decode($lesson['media'], true);
        	}

        	if ($lesson['length']) {
        		$lesson['length'] = $this->textToSeconds($lesson['length']);
        	}
        	$lesson = $this->getCourseService()->createLesson($lesson);

			return $this->render('TopxiaWebBundle:CourseLessonManage:list-item.html.twig', array(
				'course' => $course,
				'lesson' => $lesson,
			));
        }

    	$user = $this->getCurrentUser();

    	$randString = substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 12);
    	$filePath = "courselesson/{$course['id']}";
    	$fileKey = "{$filePath}/" . $randString;
    	$convertKey = $randString;

        $targetType = 'courselesson';
        $targetId = $course['id'];

    	$setting = $this->setting('storage');
    	if ($setting['upload_mode'] == 'local') {
    		$videoUploadToken = $audioUploadToken = array(
	    		'token' => $this->getUserService()->makeToken('fileupload', $user['id'], strtotime('+ 2 hours')),
	    		'url' => $this->generateUrl('uploadfile_upload', array('targetType' => $targetType, 'targetId' => $targetId)),
			);

    	} else {

    		try {

                $factory = new CloudClientFactory();
                $client = $factory->createClient();
    		
		        $commands = array_keys($client->getVideoConvertCommands());
		    	$videoUploadToken = $client->generateUploadToken($client->getBucket(), array(
		    		'convertCommands' => implode(';', $commands),
		    		'convertNotifyUrl' => $this->generateUrl('uploadfile_cloud_convert_callback', array('key' => $convertKey), true),
	    		));
	    		if (!empty($videoUploadToken['error'])) {
	    			return $this->createMessageModalResponse('error', $videoUploadToken['error']['message']);
	    		}

	    		$audioUploadToken = $client->generateUploadToken($client->getBucket(), array());
	    		if (!empty($audioUploadToken['error'])) {
	    			return $this->createMessageModalResponse('error', $audioUploadToken['error']['message']);
	    		}
    		}
    		 catch (\Exception $e) {
    			return $this->createMessageModalResponse('error', $e->getMessage());
    		}
    	}

		return $this->render('TopxiaWebBundle:CourseLessonManage:lesson-modal.html.twig', array(
			'course' => $course,
            'targetType' => $targetType,
            'targetId' => $targetId,
			'videoUploadToken' => $videoUploadToken,
			'audioUploadToken' => $audioUploadToken,
			'filePath' => $filePath,
			'fileKey' => $fileKey,
			'convertKey' => $convertKey,
			'storageSetting' => $setting,
		));
	}

	// @todo refactor it.
	public function editAction(Request $request, $courseId, $lessonId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);
		if (empty($lesson)) {
			throw $this->createNotFoundException("课时(#{$lessonId})不存在！");
		}

	    if($request->getMethod() == 'POST'){
        	$fields = $request->request->all();
        	if ($fields['media']) {
        		$fields['media'] = json_decode($fields['media'], true);
        	}
        	if ($fields['length']) {
        		$fields['length'] = $this->textToSeconds($fields['length']);
        	}

        	$fields['free'] = empty($fields['free']) ? 0 : 1;
        	$lesson = $this->getCourseService()->updateLesson($course['id'], $lesson['id'], $fields);
			return $this->render('TopxiaWebBundle:CourseLessonManage:list-item.html.twig', array(
				'course' => $course,
				'lesson' => $lesson,
			));
        }

        if ($lesson['mediaId']) {
	    	$file = $this->getUploadFileService()->getFile($lesson['mediaId']);
	    	if (!empty($file)) {
	    		$lesson['media'] = array(
		        	'id' => $file['id'],
		        	'status' => $file['convertStatus'],
		        	'source' => 'self',
		        	'name' => $file['filename'],
		        	'uri' => '',
    			);
	    	} else {
	    		$lesson['media'] = array('id' => 0, 'status' => 'none', 'source' => '', 'name' => '', 'uri' => '');
	    	}
        } else {
	        $lesson['media'] = array(
	        	'id' => 0,
	        	'status' => 'none',
	        	'source' => $lesson['mediaSource'],
	        	'name' => $lesson['mediaName'],
	        	'uri' => $lesson['mediaUri'],
	    	);
        }

        $lesson['length'] = $this->secondsToText($lesson['length']);

        $user = $this->getCurrentUser();

        $randString = substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 12);
        $filePath = "courselesson/{$course['id']}";
        $fileKey = "{$filePath}/" . $randString;
        $convertKey = $randString;

        $targetType = 'courselesson';
        $targetId = $course['id'];

    	$setting = $this->setting('storage');
    	if ($setting['upload_mode'] == 'local') {
            $videoUploadToken = $audioUploadToken = array(
                'token' => $this->getUserService()->makeToken('fileupload', $user['id'], strtotime('+ 2 hours')),
                'url' => $this->generateUrl('uploadfile_upload', array('targetType' => $targetType, 'targetId' => $targetId)),
            );
    	} else {

    		try {

                $factory = new CloudClientFactory();
                $client = $factory->createClient();

		        $commands = array_keys($client->getVideoConvertCommands());
		    	$videoUploadToken = $client->generateUploadToken($setting['cloud_bucket'], array(
		    		'convertCommands' => implode(';', $commands),
		    		'convertNotifyUrl' => $this->generateUrl('uploadfile_cloud_convert_callback', array('key' => $convertKey), true),
	    		));
	    		if (!empty($videoUploadToken['error'])) {
	    			return $this->createMessageModalResponse('error', $videoUploadToken['error']['message']);
	    		}

	    		$audioUploadToken = $client->generateUploadToken($setting['cloud_bucket'], array());
	    		if (!empty($audioUploadToken['error'])) {
	    			return $this->createMessageModalResponse('error', $audioUploadToken['error']['message']);
	    		}
    		}
    		 catch (\Exception $e) {
    			return $this->createMessageModalResponse('error', $e->getMessage());
    		}
    	}

		return $this->render('TopxiaWebBundle:CourseLessonManage:lesson-modal.html.twig', array(
			'course' => $course,
			'lesson' => $lesson,
            'targetType' => $targetType,
            'targetId' => $targetId,
			'videoUploadToken' => $videoUploadToken,
			'audioUploadToken' => $audioUploadToken,
			'filePath' => $filePath,
			'fileKey' => $fileKey,
			'convertKey' => $convertKey,
			'storageSetting' => $setting
		));
	}

	public function createTestPaperAction(Request $request, $id)
	{
		$course = $this->getCourseService()->tryManageCourse($id);

    	$papers = $this->getTestService()->findTestPapersByTarget('course', $id, 0, 1000);
    	$paperOptions = array();
    	foreach ($papers as $paper) {
    		$paperOptions[$paper['id']] = $paper['name'];
    	}

	    if($request->getMethod() == 'POST') {

            $lesson = $request->request->all();
            $lesson['type'] = 'testpaper';
            $lesson['courseId'] = $course['id'];
            $lesson = $this->getCourseService()->createLesson($lesson);

			return $this->render('TopxiaWebBundle:CourseLessonManage:list-item.html.twig', array(
				'course' => $course,
				'lesson' => $lesson,
			));

    	}

		return $this->render('TopxiaWebBundle:CourseLessonManage:testpaper-modal.html.twig', array(
			'course' => $course,
			'paperOptions' => $paperOptions,
		));
	}

	public function editTestPaperAction(Request $request, $courseId, $lessonId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);

        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);
        if (empty($lesson)) {
            throw $this->createNotFoundException("课时(#{$lessonId})不存在！");
        }

    	$papers = $this->getTestService()->findTestPapersByTarget('course', $courseId, 0, 1000);
    	$paperOptions = array();
    	foreach ($papers as $paper) {
    		$paperOptions[$paper['id']] = $paper['name'];
    	}

        if($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $lesson = $this->getCourseService()->updateLesson($course['id'], $lesson['id'], $fields);
            return $this->render('TopxiaWebBundle:CourseLessonManage:list-item.html.twig', array(
                'course' => $course,
                'lesson' => $lesson,
            ));
        }

        return $this->render('TopxiaWebBundle:CourseLessonManage:testpaper-modal.html.twig', array(
            'course' => $course,
            'lesson' => $lesson,
            'paperOptions' => $paperOptions,
        ));

	}

	public function publishAction(Request $request, $courseId, $lessonId)
	{
		$this->getCourseService()->publishLesson($courseId, $lessonId);
		return $this->render('TopxiaWebBundle:CourseLessonManage:list-item.html.twig', array(
			'course' => $this->getCourseService()->getCourse($courseId),
			'lesson' => $this->getCourseService()->getCourseLesson($courseId, $lessonId),
		));
	}

	public function unpublishAction(Request $request, $courseId, $lessonId)
	{
		$this->getCourseService()->unpublishLesson($courseId, $lessonId);
		return $this->render('TopxiaWebBundle:CourseLessonManage:list-item.html.twig', array(
			'course' => $this->getCourseService()->getCourse($courseId),
			'lesson' => $this->getCourseService()->getCourseLesson($courseId, $lessonId),
		));
	}

	public function sortAction(Request $request, $id)
	{
		$course = $this->getCourseService()->tryManageCourse($id);
		$this->getCourseService()->sortCourseItems($course['id'], $request->request->get('ids'));
		return $this->createJsonResponse(true);
	}

	public function deleteAction(Request $request, $courseId, $lessonId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$this->getCourseService()->deleteLesson($course['id'], $lessonId);
		$this->getCourseMaterialService()->deleteMaterialsByLessonId($lessonId);
		return $this->createJsonResponse(true);
	}

	private function secondsToText($value)
	{
        $minutes = intval($value / 60);
        $seconds = $value - $minutes * 60;
        return sprintf('%02d', $minutes) . ':' . sprintf('%02d', $seconds);
	}

	private function textToSeconds($text)
	{
		if (strpos($text, ':') === false) {
			return 0;
		}
		list($minutes, $seconds) = explode(':', $text, 2);
		return intval($minutes) * 60 + intval($seconds);
	}

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getTestService()
    {
        return $this->getServiceKernel()->createService('Quiz.TestService');
    }

    private function getCourseMaterialService()
    {
        return $this->getServiceKernel()->createService('Course.MaterialService');
    }

    private function getDiskService()
    {
        return $this->getServiceKernel()->createService('User.DiskService');
    }

    private function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

}