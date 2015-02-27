<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\CloudClientFactory;
use Topxia\Service\Util\LiveClientFactory;
use Topxia\Common\Paginator;

class CourseLessonManageController extends BaseController
{
	public function indexAction(Request $request, $id)
	{
		$course = $this->getCourseService()->tryManageCourse($id);
		$courseItems = $this->getCourseService()->getCourseItems($course['id']);

		$lessonIds = ArrayToolkit::column($courseItems, 'id');

		if ($this->isPluginInstalled('Homework')) {
			$exercises = $this->getServiceKernel()->createService('Homework:Homework.ExerciseService')->findExercisesByLessonIds($lessonIds);
			$homeworks = $this->getServiceKernel()->createService('Homework:Homework.HomeworkService')->findHomeworksByCourseIdAndLessonIds($course['id'], $lessonIds);
		}

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
		$default = $this->getSettingService()->get('default', array());
		return $this->render('TopxiaWebBundle:CourseLessonManage:index.html.twig', array(
			'course' => $course,
			'items' => $courseItems,
			'exercises' => empty($exercises) ? array() : $exercises,
			'homeworks' => empty($homeworks) ? array() : $homeworks,
			'files' => ArrayToolkit::index($files,'id'),
			'default'=> $default
		));
	}

	public function viewDraftAction(Request $request)
	    {
	        $params =  $request->query->all();

	        $courseId = $params['courseId'];
	        if(array_key_exists('lessonId', $params)){
	            $lessonId = $params['lessonId'];
	        } else {
	            $lessonId = 0;
	        }

	        $user = $this->getCurrentUser();
	        $userId = $user['id'];
	        $drafts = $this->getCourseService()->findCourseDraft($courseId,$lessonId,$userId);
	        $listdrafts=array("title"=>$drafts['title'],"summary"=>$drafts['summary'],"content"=>$drafts['content']);         
	        return $this->createJsonResponse($listdrafts);
	    }

	    public function draftCreateAction(Request $request)
	    {
	        $formData = $request->request->all();
	        $user = $this->getCurrentUser();
	        $userId = $user['id'];
	        $courseId = $formData['courseId'];
	        if(array_key_exists('lessonId', $formData)){
	            $lessonId = $formData['lessonId'];
	        } else {
	            $lessonId = 0;
	            $formData['lessonId'] = 0;
	        }

	        $content = $formData['content'];

	        $drafts = $this->getCourseService()->findCourseDraft($courseId,$lessonId,$userId);
	        if($drafts) {
	            $draft = $this->getCourseService()->updateCourseDraft($courseId,$lessonId,$userId,$formData);
	        } else {
	            $draft = $this->getCourseService()->createCourseDraft($formData);
	        }
	        return $this->createJsonResponse(true);
	    }


	// @todo refactor it.
	public function createAction(Request $request, $id)
	{
		$course = $this->getCourseService()->tryManageCourse($id);
		$parentId = $request->query->get('parentId');
			if($request->getMethod() == 'POST') {
				$lesson = $request->request->all();
				$lesson['courseId'] = $course['id'];

			if ($lesson['media']) {
				$lesson['media'] = json_decode($lesson['media'], true);
			}
			if (is_numeric($lesson['second'])) {
				$lesson['length'] = $this->textToSeconds($lesson['minute'], $lesson['second']);
				unset($lesson['minute']);
				unset($lesson['second']);
			}
			$lesson = $this->getCourseService()->createLesson($lesson);
			
			$file = false;
			if ($lesson['mediaId'] > 0 && ($lesson['type'] != 'testpaper')) {
				$file = $this->getUploadFileService()->getFile($lesson['mediaId']);

				if($file['type']=="document" && $file['convertStatus'] == "none" ){

		            $convertHash = $this->getUploadFileService()->reconvertFile(
		                $file['id'],
		                $this->generateUrl('uploadfile_cloud_convert_callback2', array(), true)
		            );
				}

				$lesson['mediaStatus'] = $file['convertStatus'];
			}
					  //  if ($shortcut == 'true') 
							  //  return $this->render('TopxiaWebBundle:CourseLessonManage:list-item.html.twig', array( 'course' => $course,'lesson' => $lesson))->getContent();                        
						//else
			$lessonId =0;
        	$this->getCourseService()->deleteCourseDrafts($id,$lessonId,$this->getCurrentUser()->id);
			
			return $this->render('TopxiaWebBundle:CourseLessonManage:list-item.html.twig', array(
				'course' => $course,
				'lesson' => $lesson,
				'file' => $file
			));
		}

		$user = $this->getCurrentUser();
		$userId = $user['id'];
		$randString = substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 12);
		$filePath = "courselesson/{$course['id']}";
		$fileKey = "{$filePath}/" . $randString;
		$convertKey = $randString;

		$targetType = 'courselesson';
		$targetId = $course['id'];
		$draft = $this->getCourseService()->findCourseDraft($targetId, 0, $userId);
		$setting = $this->setting('storage');
   //  	if ($setting['upload_mode'] == 'local') {
   //  		$videoUploadToken = $audioUploadToken = $pptUploadToken = array(
	  //   		'token' => $this->getUserService()->makeToken('fileupload', $user['id'], strtotime('+ 2 hours')),
	  //   		'url' => $this->generateUrl('uploadfile_upload', array('targetType' => $targetType, 'targetId' => $targetId)),
			// );

   //  	} else {

   //  	}

		$features = $this->container->hasParameter('enabled_features') ? $this->container->getParameter('enabled_features') : array();

		return $this->render('TopxiaWebBundle:CourseLessonManage:lesson-modal.html.twig', array(
			'course' => $course,
			'targetType' => $targetType,
			'targetId' => $targetId,
			'filePath' => $filePath,
			'fileKey' => $fileKey,
			'convertKey' => $convertKey,
			'storageSetting' => $setting,
			'features' => $features,
			'parentId'=>$parentId,
			'draft' => $draft
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

			if ($fields['second']) {
				$fields['length'] = $this->textToSeconds($fields['minute'], $fields['second']);
				unset($fields['minute']);
				unset($fields['second']);
			}

			$fields['free'] = empty($fields['free']) ? 0 : 1;
			$lesson = $this->getCourseService()->updateLesson($course['id'], $lesson['id'], $fields);
			$this->getCourseService()->deleteCourseDrafts($course['id'],$lesson['id'], $this->getCurrentUser()->id);
			
			$file = false;
			if ($lesson['mediaId'] > 0 && ($lesson['type'] != 'testpaper')) {
				$file = $this->getUploadFileService()->getFile($lesson['mediaId']);
				$lesson['mediaStatus'] = $file['convertStatus'];
				if($file['type']=="document" && $file['convertStatus'] == "none" ){

		            $convertHash = $this->getUploadFileService()->reconvertFile(
		                $file['id'],
		                $this->generateUrl('uploadfile_cloud_convert_callback2', array(), true)
		            );
				}
			}
			
			return $this->render('TopxiaWebBundle:CourseLessonManage:list-item.html.twig', array(
				'course' => $course,
				'lesson' => $lesson,
				'file' => $file
			));
		}

		$file = null;
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
				$lesson['media'] = array('id' => 0, 'status' => 'none', 'source' => '', 'name' => '文件已删除', 'uri' => '');
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

		list($lesson['minute'], $lesson['second']) = $this->secondsToText($lesson['length']);

		$user = $this->getCurrentUser();
		$userId = $user['id'];
		$randString = substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 12);
		$filePath = "courselesson/{$course['id']}";
		$fileKey = "{$filePath}/" . $randString;
		$convertKey = $randString;

		$targetType = 'courselesson';
		$targetId = $course['id'];
		$draft = $this->getCourseService()->findCourseDraft($courseId,$lessonId, $userId);
		$setting = $this->setting('storage');
		if ($setting['upload_mode'] == 'local') {
			// $videoUploadToken = $audioUploadToken = $pptUploadToken = array(
			//     'token' => $this->getUserService()->makeToken('fileupload', $user['id'], strtotime('+ 2 hours')),
			//     'url' => $this->generateUrl('uploadfile_upload', array('targetType' => $targetType, 'targetId' => $targetId)),
			// );
		} else {

		}
		$lesson['title'] = str_replace(array('"',"'"), array('&#34;','&#39;'), $lesson['title']);

		$features = $this->container->hasParameter('enabled_features') ? $this->container->getParameter('enabled_features') : array();
		
		return $this->render('TopxiaWebBundle:CourseLessonManage:lesson-modal.html.twig', array(
			'course' => $course,
			'lesson' => $lesson,
			'file' => $file,
			'targetType' => $targetType,
			'targetId' => $targetId,
			'filePath' => $filePath,
			'fileKey' => $fileKey,
			'convertKey' => $convertKey,
			'storageSetting' => $setting,
			'features' => $features,
			'draft' => $draft
		));
	}

	public function createTestPaperAction(Request $request, $id)
	{
		$course = $this->getCourseService()->tryManageCourse($id);
						  $parentId = $request->query->get('parentId');
		$conditions = array();
		$conditions['target'] = "course-{$course['id']}";
		$conditions['status'] = 'open';

		$testpapers = $this->getTestpaperService()->searchTestpapers(
			$conditions,
			array('createdTime' ,'DESC'),
			0,
			1000
		);

		$paperOptions = array();
		foreach ($testpapers as $testpaper) {
			$paperOptions[$testpaper['id']] = $testpaper['name'];
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

		$features = $this->container->hasParameter('enabled_features') ? $this->container->getParameter('enabled_features') : array();

		return $this->render('TopxiaWebBundle:CourseLessonManage:testpaper-modal.html.twig', array(
			'course' => $course,
			'paperOptions' => $paperOptions,
									   'features' => $features,
									   'parentId' =>$parentId
		));
	}

	public function editTestpaperAction(Request $request, $courseId, $lessonId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);

		$lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);
		if (empty($lesson)) {
			throw $this->createNotFoundException("课时(#{$lessonId})不存在！");
		}

		$conditions = array();
		$conditions['target'] = "course-{$course['id']}";
		$conditions['status'] = 'open';

		$testpapers = $this->getTestpaperService()->searchTestpapers(
			$conditions,
			array('createdTime' ,'DESC'),
			0,
			1000
		);

		$paperOptions = array();
		foreach ($testpapers as $paper) {
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

		$features = $this->container->hasParameter('enabled_features') ? $this->container->getParameter('enabled_features') : array();

		return $this->render('TopxiaWebBundle:CourseLessonManage:testpaper-modal.html.twig', array(
			'course' => $course,
			'lesson' => $lesson,
			'paperOptions' => $paperOptions,
			'features' => $features,

		));

	}

	public function publishAction(Request $request, $courseId, $lessonId)
	{
		$this->getCourseService()->publishLesson($courseId, $lessonId);
		$course = $this->getCourseService()->getCourse($courseId);
		$lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);

		$file = false;
		if ($lesson['mediaId'] > 0 && ($lesson['type'] != 'testpaper')) {
			$file = $this->getUploadFileService()->getFile($lesson['mediaId']);
			$lesson['mediaStatus'] = $file['convertStatus'];
		}

		return $this->render('TopxiaWebBundle:CourseLessonManage:list-item.html.twig', array(
			'course' => $course,
			'lesson' => $lesson,
			'file' => $file
		));
	}

	public function unpublishAction(Request $request, $courseId, $lessonId)
	{
		$this->getCourseService()->unpublishLesson($courseId, $lessonId);

		$course = $this->getCourseService()->getCourse($courseId);
		$lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
		$file = false;
		if ($lesson['mediaId'] > 0 && ($lesson['type'] != 'testpaper')) {
			$file = $this->getUploadFileService()->getFile($lesson['mediaId']);
			$lesson['mediaStatus'] = $file['convertStatus'];
		}

		return $this->render('TopxiaWebBundle:CourseLessonManage:list-item.html.twig', array(
			'course' => $course,
			'lesson' => $lesson,
			'file' => $file
		));
	}

	public function sortAction(Request $request, $id)
	{
		$ids = $request->request->get('ids');
		if(!empty($ids)){
			$course = $this->getCourseService()->tryManageCourse($id);
			$this->getCourseService()->sortCourseItems($course['id'], $request->request->get('ids'));
		}
		return $this->createJsonResponse(true);
	}

	public function deleteAction(Request $request, $courseId, $lessonId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
		if($course['type']=='live'){
			$client = LiveClientFactory::createClient();
			$result = $client->deleteLive($lesson['mediaId'], $lesson['liveProvider']);
			$this->getCourseService()->deleteCourseLessonReplayByLessonId($lessonId);
		}
		$this->getCourseService()->deleteLesson($course['id'], $lessonId);
		$this->getCourseMaterialService()->deleteMaterialsByLessonId($lessonId);
		
		return $this->createJsonResponse(true);
	}

	private function secondsToText($value)
	{
		$minutes = intval($value / 60);
		$seconds = $value - $minutes * 60;
		return array($minutes, $seconds);
	}

	private function textToSeconds($minutes, $seconds)
	{
		return intval($minutes) * 60 + intval($seconds);
	}

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
    
    private function getAppService(){
		return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }
    
    private function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper.TestpaperService');
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
    
    private function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

    private function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

}
