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
		$exercises = $this->getExerciseService()->findExerciseByCourseIdAndLessonIds($course['id'], $lessonIds);
		$homeworks = $this->getHomeworkService()->findHomeworksByCourseIdAndLessonIds($course['id'], $lessonIds);
		foreach ($homeworks as &$homework) {
			$homework['results'] = $this->getHomeworkService()->searchHomeworkResultsCount(array( 'courseId' => $homework['courseId'], 'lessonId' => $homework['lessonId'], 'status' => 'reviewing' ));
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
		
		return $this->render('TopxiaWebBundle:CourseLessonManage:index.html.twig', array(
			'course' => $course,
			'items' => $courseItems,
			'exercises' => $exercises,
			'homeworks' => $homeworks,
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
        	if (is_numeric($lesson['second'])) {
        		$lesson['length'] = $this->textToSeconds($lesson['minute'], $lesson['second']);
        		unset($lesson['minute']);
        		unset($lesson['second']);
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
    		$videoUploadToken = $audioUploadToken = $pptUploadToken = array(
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

                $commands = array_keys($client->getPPTConvertCommands());
                $pptUploadToken = $client->generateUploadToken($client->getBucket(), array(
                    'convertCommands' => implode(';', $commands),
                    'convertNotifyUrl' => $this->generateUrl('uploadfile_cloud_convert_callback', array('key' => $convertKey, 'twoStep' => '1'), true),
                ));
                if (!empty($pptUploadToken['error'])) {
                    return $this->createMessageModalResponse('error', $pptUploadToken['error']['message']);
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
			'pptUploadToken' => $pptUploadToken,
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
        	if ($fields['second']) {
        		$fields['length'] = $this->textToSeconds($fields['minute'], $fields['second']);
        		unset($fields['minute']);
        		unset($fields['second']);
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

        list($lesson['minute'], $lesson['second']) = $this->secondsToText($lesson['length']);

        $user = $this->getCurrentUser();

        $randString = substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 12);
        $filePath = "courselesson/{$course['id']}";
        $fileKey = "{$filePath}/" . $randString;
        $convertKey = $randString;

        $targetType = 'courselesson';
        $targetId = $course['id'];

    	$setting = $this->setting('storage');
    	if ($setting['upload_mode'] == 'local') {
            $videoUploadToken = $audioUploadToken = $pptUploadToken = array(
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

                $commands = array_keys($client->getPPTConvertCommands());
                $pptUploadToken = $client->generateUploadToken($client->getBucket(), array(
                    'convertCommands' => implode(';', $commands),
                    'convertNotifyUrl' => $this->generateUrl('uploadfile_cloud_convert_callback', array('key' => $convertKey, 'twoStep' => 1), true),
                ));
                if (!empty($pptUploadToken['error'])) {
                    return $this->createMessageModalResponse('error', $pptUploadToken['error']['message']);
                }

    		}
    		 catch (\Exception $e) {
    			return $this->createMessageModalResponse('error', $e->getMessage());
    		}
    	}
        $lesson['title'] = str_replace(array('"',"'"), array('&#34;','&#39;'), $lesson['title']);
		return $this->render('TopxiaWebBundle:CourseLessonManage:lesson-modal.html.twig', array(
			'course' => $course,
			'lesson' => $lesson,
            'targetType' => $targetType,
            'targetId' => $targetId,
			'videoUploadToken' => $videoUploadToken,
			'audioUploadToken' => $audioUploadToken,
            'pptUploadToken' => $pptUploadToken,
			'filePath' => $filePath,
			'fileKey' => $fileKey,
			'convertKey' => $convertKey,
			'storageSetting' => $setting
		));
	}

	public function createTestPaperAction(Request $request, $id)
	{
		$course = $this->getCourseService()->tryManageCourse($id);

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

		return $this->render('TopxiaWebBundle:CourseLessonManage:testpaper-modal.html.twig', array(
			'course' => $course,
			'paperOptions' => $paperOptions,
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

        return $this->render('TopxiaWebBundle:CourseLessonManage:testpaper-modal.html.twig', array(
            'course' => $course,
            'lesson' => $lesson,
            'paperOptions' => $paperOptions,
        ));

	}

	public function createExerciseAction(Request $request, $courseId, $lessonId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);

        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);
        if (empty($lesson)) {
            throw $this->createNotFoundException("课时(#{$lessonId})不存在！");
        }

        if($request->getMethod() == 'POST') {
        	$fields = $request->request->all();
        	$fields = $this->generateExerciseFields($fields, $course, $lesson);

        	list($exercise, $items) = $this->getExerciseService()->createExercise($fields);
        	return $this->createJsonResponse(true);
        }

		return $this->render('TopxiaWebBundle:CourseLessonManage:exercise.html.twig', array(
			'course' => $course,
			'lesson' => $lesson,
			'exercise' => array('id' => null)
		));
	}

	public function updateExerciseAction(Request $request, $courseId, $lessonId, $id)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);

        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);
        if (empty($lesson)) {
            throw $this->createNotFoundException("课时(#{$lessonId})不存在！");
        }

        $exercise = $this->getExerciseService()->getExercise($id);
        if (empty($exercise)) {
        	throw $this->createNotFoundException("练习(#{$id})不存在！");
        }

        if($request->getMethod() == 'POST') {
        	$fields = $request->request->all();
        	$fields = $this->generateExerciseFields($fields, $course, $lesson);

        	list($exercise, $items) = $this->getExerciseService()->updateExercise($exercise['id'], $fields);
        	return $this->createJsonResponse(true);
        }
        
        return $this->render('TopxiaWebBundle:CourseLessonManage:exercise.html.twig', array(
			'course' => $course,
			'lesson' => $lesson,
			'exercise' => $exercise
		));
	}

	public function deleteExerciseAction(Request $request, $courseId, $lessonId, $id)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);

        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);
        if (empty($lesson)) {
            throw $this->createNotFoundException("课时(#{$lessonId})不存在！");
        }
        $exercise = $this->getExerciseService()->getExercise($id);
        if (empty($exercise)) {
        	throw $this->createNotFoundException("练习(#{$id})不存在！");
        }
        $this->getExerciseService()->deleteExercise($exercise['id']);

        return $this->createJsonResponse(true);
	}

	public function homeworkListAction(Request $request, $courseId, $lessonId)
	{
		$status = $request->query->get('status', 'unchecked');

		$course = $this->getCourseService()->tryManageCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);
        if (empty($lesson)) {
            throw $this->createNotFoundException("课时(#{$lessonId})不存在！");
        }

		$homework = $this->getHomeworkService()->getHomeworkByCourseIdAndLessonId($course['id'], $lesson['id']);

		$conditions = array('courseId' => $homework['courseId'], 'role' => 'student');
		$paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchMemberCount($conditions)
            , 25
        );

		$students = $this->getCourseService()->searchMembers(
			$conditions, 
			array('createdTime', 'DESC'), 
			$paginator->getOffsetCount(),
            $paginator->getPerPageCount());

		$studentUserIds = ArrayToolkit::column($students, 'userId');
		$users = $this->getUserService()->findUsersByIds($studentUserIds);
		$homeworkResults = ArrayToolkit::index($this->getHomeworkService()->findHomeworkResultsByCourseIdAndLessonId($course['id'], $lesson['id']), 'userId');

		if (!empty($homeworkResults)) {
			$students = $this->getHomeworkStudents($status, $students, $homeworkResults);
		} else {
			if ($status != 'uncommitted') {
				$students = array();
			}
		}

        $committedCount = $this->getHomeworkService()->searchHomeworkResultsCount(array(
            'commitStatus' => 'committed',
            'courseId' => $course['id'],
            'lessonId' => $lesson['id']
        ));
        $uncommitCount = $this->getCourseService()->searchMemberCount($conditions) - $committedCount;
        $reviewingCount = $this->getHomeworkService()->searchHomeworkResultsCount(array(
            'status' => 'reviewing',
            'courseId' => $course['id'],
            'lessonId' => $lesson['id']
        ));
        $finishedCount = $this->getHomeworkService()->searchHomeworkResultsCount(array(
            'status' => 'finished',
            'courseId' => $course['id'],
            'lessonId' => $lesson['id']
        ));

		return $this->render('TopxiaWebBundle:CourseLessonManage:homework-list.html.twig', array(
			'course' => $course,
			'lesson' => $lesson,
			'status' => $status,
			'homework' => empty($homework) ? array() : $homework,
			'students' => $students,
			'users' => $users,
			'homeworkResults' => $homeworkResults,
			'paginator' => $paginator,
            'uncommitCount' => $uncommitCount,
            'reviewingCount' => $reviewingCount,
            'finishedCount' => $finishedCount
		));
	}

	private function getHomeworkStudents($status, $students, $homeworkResults)
	{
		if ($status == 'uncommitted') {
			foreach ($students as &$student) {
				foreach ($homeworkResults as $item) {
					if ($item['status'] != 'doing' && $item['userId'] == $student['userId'] ) {
						$student = null;
					}
				}
			}
		} 

		if ($status == 'unchecked') {
			foreach ($students as &$student) {
				$key = false;
				foreach ($homeworkResults as $item) {
					if ($item['status'] == 'reviewing' && $item['userId'] == $student['userId'] ) {
						$key = true;
					}
				}

				if ($key == true) {
					continue;
				}
				$student = null;
			}
		}

		if ($status == 'checked') {
			foreach ($students as &$student) {
				$key = false;
				foreach ($homeworkResults as $item) {
					if ($item['status'] == 'finished' && $item['userId'] == $student['userId'] ) {
						$key = true;
					}
				}

				if ($key == true) {
					continue;
				}
				$student = null;
			}
		}
		return array_filter($students);
	}

	private function generateExerciseFields($fields, $course, $lesson)
	{
		$fields['ranges'] = array();
    	$fields['choice'] = empty($fields['choice']) ? array() : $fields['ranges'][] = $fields['choice'];
    	$fields['single_choice'] = empty($fields['single_choice']) ? array() : $fields['ranges'][] = $fields['single_choice'];
    	$fields['uncertain_choice'] = empty($fields['uncertain_choice']) ? array() : $fields['ranges'][] = $fields['uncertain_choice'];
    	$fields['fill'] = empty($fields['fill']) ? array() : $fields['ranges'][] = $fields['fill'];
    	$fields['determine'] = empty($fields['determine']) ? array() : $fields['ranges'][] = $fields['determine'];
    	$fields['essay'] = empty($fields['essay']) ? array() : $fields['ranges'][] = $fields['essay'];
    	$fields['material'] = empty($fields['material']) ? array() : $fields['ranges'][] = $fields['material'];
    	$fields['courseId'] = $course['id'];
    	$fields['lessonId'] = $lesson['id'];

    	return $fields;
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
		$lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
		$client = LiveClientFactory::createClient();
        $client->deleteLive($lesson['mediaId']);
		$this->getCourseService()->deleteLesson($course['id'], $lessonId);
		$this->getCourseMaterialService()->deleteMaterialsByLessonId($lessonId);
		return $this->createJsonResponse(true);
	}

	private function sortType($types)
	{
		$newTypes = array('single_choice','choice','uncertain_choice','fill','determine','essay','material');
		
		foreach ($types as $key => $value) {
			if (!in_array($value, $newTypes)) {
				$k = array_search($value,$newTypes);
				unset($newTypes[$k]);
			}
		}
		return $newTypes;
	}

	private function getclassifyQuestionsAndCount($questions)
	{
		$singleNum = 0;
		$choiceNum = 0;
		$uncertainChoiceNum = 0;
		$fillNum = 0;
		$determineNum = 0;
		$essayNum = 0;
		$materialNum = 0;

		$num = 1;

		$newQuetsions = array();
		$typeQuestionsNum = array();

		foreach ($questions as $key => $value) {
			
			if ($value['type'] == 'single_choice') {
				$value['number'] = $num;
				$newQuetsions['single_choice'][] = $value;
				$singleNum++;
				
			}elseif ($value['type'] == 'choice') {
				$value['number'] = $num;
				$newQuetsions['choice'][] = $value;
				$choiceNum++;
			}elseif ($value['type'] == 'uncertain_choice') {
				$value['number'] = $num;
				$newQuetsions['uncertain_choice'][] = $value;
				$uncertainChoiceNum++;
			}elseif ($value['type'] == 'fill') {
				$value['number'] = $num;
				$newQuetsions['fill'][] = $value;
				$fillNum++;
			}elseif ($value['type'] == 'determine') {
				$value['number'] = $num;
				$newQuetsions['determine'][] = $value;
				$determineNum++;
			}elseif ($value['type'] == 'essay') {
				$value['number'] = $num;
				$newQuetsions['essay'][] = $value;
				$essayNum++;
			}elseif ($value['type'] == 'material') {
			
					if ($value['subCount']>0) {
						$questions = $this->getQuestionService()->findQuestionsByParentId($value['id']);
						foreach ($questions as $key => $questionValue) {
							$questionValue['number'] = $num;
							$questions[$key] = $questionValue;
							$num++;
						}
						$value['subQuestions'] = $questions;
					} else {
							$value['number'] = $num;
						$value['subQuestions'] = array();
					}
				$newQuetsions['material'][] = $value;
				$materialNum++;
			}

			$num++;
		}
		$typeQuestionsNum['single_choice'] = $singleNum;
		$typeQuestionsNum['choice'] = $choiceNum;
		$typeQuestionsNum['uncertain_choice'] = $uncertainChoiceNum;
		$typeQuestionsNum['fill'] = $fillNum;
		$typeQuestionsNum['determine'] = $determineNum;
		$typeQuestionsNum['essay'] = $essayNum;
		$typeQuestionsNum['material'] = $materialNum;

		return array(
			'typeQuestionsNum' => array_filter($typeQuestionsNum),
			'newQuetsions' => $newQuetsions,
		);
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
    
    private function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Course.HomeworkService');
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

    private function getExerciseService()
    {
    	return $this->getServiceKernel()->createService('Course.ExerciseService');
    }

    private function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

}