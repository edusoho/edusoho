<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\CloudClient;

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
		$files = $this->getDiskService()->findFilesByIds($mediaIds);
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
    	$filePath = "course-{$course['id']}";
    	$fileKey = "{$filePath}/" . $randString;
    	$convertKey = $randString;


    	$setting = $this->setting('storage');
    	if ($setting['upload_mode'] == 'local') {
    		$uploadToken = $this->getUserService()->makeToken('diskLocalUpload', $user['id'], strtotime('+ 2 hours'));
    	} else {

	        $client = new CloudClient(
	            $setting['cloud_access_key'],
	            $setting['cloud_secret_key'],
	            $setting['cloud_bucket'],
	            $setting['cloud_bucket_domain'],
	            $setting['cloud_mac_index'],
	            $setting['cloud_mac_key']
	        );

	        $commands = array_keys($client->getVideoConvertCommands());
	    	$uploadToken = $client->generateUploadToken(array(
	    		'PersistentOps' => implode(';', $commands),
	    		'PersistentNotifyUrl' => $this->generateUrl('disk_convert_callback', array('key' => $convertKey)),
    		));
    	}

		return $this->render('TopxiaWebBundle:CourseLessonManage:lesson-modal.html.twig', array(
			'course' => $course,
			'uploadToken' => $uploadToken,
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
	    	$file = $this->getDiskService()->getFile($lesson['mediaId']);
	    	if (!empty($file)) {
	    		$lesson['media'] = array(
		        	'id' => 0,
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
    	$filePath = "course-{$course['id']}";
    	$fileKey = "{$filePath}/" . $randString;
    	$convertKey = $randString;

    	$setting = $this->setting('storage');
    	if ($setting['upload_mode'] == 'local') {
    		$uploadToken = $this->getUserService()->makeToken('diskLocalUpload', $user['id'], strtotime('+ 2 hours'));
    	} else {
	        $client = new CloudClient(
	            $setting['cloud_access_key'],
	            $setting['cloud_secret_key'],
	            $setting['cloud_bucket'],
	            $setting['cloud_bucket_domain'],
	            $setting['cloud_mac_index'],
	            $setting['cloud_mac_key']
	        );

	        $commands = array_keys($client->getVideoConvertCommands());
	    	$uploadToken = $client->generateUploadToken(array(
	    		'PersistentOps' => implode(';', $commands),
	    		'PersistentNotifyUrl' => $this->generateUrl('disk_convert_callback', array('key' => $convertKey)),
    		));
    	}

		return $this->render('TopxiaWebBundle:CourseLessonManage:lesson-modal.html.twig', array(
			'course' => $course,
			'lesson' => $lesson,
			'uploadToken' => $uploadToken,
			'filePath' => $filePath,
			'fileKey' => $fileKey,
			'convertKey' => $convertKey,
			'storageSetting' => $setting
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

    private function getCourseMaterialService()
    {
        return $this->getServiceKernel()->createService('Course.MaterialService');
    }

    private function getDiskService()
    {
        return $this->getServiceKernel()->createService('User.DiskService');
    }

}