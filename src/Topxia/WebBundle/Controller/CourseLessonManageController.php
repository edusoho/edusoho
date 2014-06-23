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

		$students = $this->getCourseService()->searchMembers(array('courseId' => $homework['courseId'], 'role' => 'student'), array('createdTime', 'DESC'), 0, 100);
		$studentUserIds = ArrayToolkit::column($students, 'userId');
		$users = $this->getUserService()->findUsersByIds($studentUserIds);
		$homeworkResults = $this->getHomeworkService()->findHomeworkResultsByCourseIdAndLessonId($course['id'], $lesson['id']);

		if (!empty($homeworkResults)) {
			$students = $this->getHomeworkStudents($status, $students, $homeworkResults);
		} else {
			if ($status != 'uncommitted') {
				$students = array();
			}
		}

		return $this->render('TopxiaWebBundle:CourseLessonManage:homework-check.html.twig', array(
			'course' => $course,
			'lesson' => $lesson,
			'status' => $status,
			'homework' => $homework,
			'students' => $students,
			'users' => $users
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

	public function createHomeworkAction(Request $request, $courseId, $lessonId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);
		$user = $this->getCurrentUser();

        if(!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

		if (empty($course)) {
			throw $this->createNotFoundException("课程(#{$courseId})不存在！");
		}

		if (empty($lesson)) {
			throw $this->createNotFoundException("课时(#{$lessonId})不存在！");
		}

		if ($request->getMethod() == 'POST') {

        	$fields = $request->request->all();
        	$homework = $this->getHomeworkService()->createHomework($courseId,$lessonId,$fields);

	        if($homework){
	            return $this->createJsonResponse(array("status" =>"success",'courseId'=>$courseId));
	        } else {
	            return $this->createJsonResponse(array("status" =>"failed")); 
	        }
		}

		return $this->render('TopxiaWebBundle:CourseLessonManage:homework-modal.html.twig', array(
            'course' => $course,
            'lesson' => $lesson,
        ));
	}

	public function editHomeworkAction(Request $request,$courseId,$lessonId,$homeworkId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);
		$user = $this->getCurrentUser();

        if(!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

		if (empty($course)) {
			throw $this->createNotFoundException("课程(#{$courseId})不存在！");
		}

		if (empty($lesson)) {
			throw $this->createNotFoundException("课时(#{$lessonId})不存在！");
		}

		$homework = $this->getHomeworkService()->getHomework($homeworkId);

		if (empty($homework)) {
			throw $this->createNotFoundException("作业(#{$homeworkId})不存在！");
		}

		$homeworkItems = $this->getHomeworkService()->findHomeworkItemsByHomeworkId($homeworkId);
		$questions = $this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($homeworkItems, 'questionId'));
        
		if ($request->getMethod() == 'POST') {
			
			$fields = $request->request->all();
			$homework = $this->getHomeworkService()->updateHomework($homeworkId, $fields);

	        if($homework){
	            return $this->createJsonResponse(array("status" =>"success",'courseId'=>$courseId));
	        } else {
	            return $this->createJsonResponse(array("status" =>"failed")); 
	        }
		}

		return $this->render('TopxiaWebBundle:CourseLessonManage:homework-modal.html.twig', array(
            'course' => $course,
            'lesson' => $lesson,
            'homework' => $homework,
            'homeworkItems' => $homeworkItems,
            'questions' => $questions,
        ));
	}

	public function removeHomeworkAction(Request $request,$courseId,$lessonId,$homeworkId)
	{
		$result = $this->getHomeworkService()->removeHomework($homeworkId);

        if($result){
            return $this->createJsonResponse(array("status" =>"success"));
        } else {
            return $this->createJsonResponse(array("status" =>"failed")); 
	    }

	}

	public function homeworkItemsAction(Request $request,$courseId,$homeworkId)
	{
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $homework = $this->getHomeworkService()->getHomework($homeworkId);
        if(empty($homework)){
            throw $this->createNotFoundException('作业不存在');
        }

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            var_dump($data);exit();
            if (empty($data['questionId']) or empty($data['scores'])) {
                return $this->createMessageResponse('error', '试卷题目不能为空！');
            }
            if (count($data['questionId']) != count($data['scores'])) {
                return $this->createMessageResponse('error', '试卷题目数据不正确');
            }

            $data['questionId'] = array_values($data['questionId']);
            $data['scores'] = array_values($data['scores']);

            $items = array();
            foreach ($data['questionId'] as $index => $questionId) {
                $items[] = array('questionId' => $questionId, 'score' => $data['scores'][$index]);
            }

            $this->getTestpaperService()->updateTestpaperItems($testpaper['id'], $items);

            $this->setFlashMessage('success', '试卷题目保存成功！');
            return $this->redirect($this->generateUrl('course_manage_testpaper',array( 'courseId' => $courseId)));
        }

        $items = $this->getHomeworkService()->getHomeworkItems($homework['id']);
        $questions = $this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($items, 'questionId'));

        $targets = $this->get('topxia.target_helper')->getTargets(ArrayToolkit::column($questions, 'target'));

        $subItems = array();
        foreach ($items as $key => $item) {
            if ($item['parentId'] > 0) {
                $subItems[$item['parentId']][] = $item;
                unset($items[$key]);
            }
        }


        return $this->render('TopxiaWebBundle:CourseLessonManage:homework-items.html.twig', array(
            'course' => $course,
            'homework' => $homework,
            'items' => ArrayToolkit::group($items, 'questionType'),
            'subItems' => $subItems,
            'questions' => $questions,
            'targets' => $targets,
        ));
	}

	public function homeworkItemPickerAction(Request $request,$courseId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);

        $conditions = $request->query->all();

        if (empty($conditions['target'])) {
            $conditions['targetPrefix'] = "course-{$course['id']}";
        }

        $conditions['parentId'] = 0;
        $conditions['excludeIds'] = empty($conditions['excludeIds']) ? array() : explode(',', $conditions['excludeIds']);

        if (!empty($conditions['keyword'])) {
            $conditions['stem'] = $conditions['keyword'];
        }


        $replace = empty($conditions['replace']) ? '' : $conditions['replace'];

        $paginator = new Paginator(
            $request,
            $this->getQuestionService()->searchQuestionsCount($conditions),
            7
        );

        $questions = $this->getQuestionService()->searchQuestions(
                $conditions, 
                array('createdTime' ,'DESC'), 
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
        );

        $targets = $this->get('topxia.target_helper')->getTargets(ArrayToolkit::column($questions, 'target'));

        return $this->render('TopxiaWebBundle:CourseLessonManage:homework-items-picker.html.twig', array(
            'course' => $course,
            'questions' => $questions,
            'replace' => $replace,
            'paginator' => $paginator,
            'targetChoices' => $this->getQuestionRanges($course, true),
            'targets' => $targets,
            'conditions' => $conditions,
        ));
	}

	public function homeworkItemPickedAction(Request $request,$courseId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);

        $question = $this->getQuestionService()->getQuestion($request->query->get('questionId'));
        if (empty($question)) {
            throw $this->createNotFoundException();
        }
        //add homework_item
		// $items['homeworkId'] = $homeworkId;
		// $items['questionId'] = $question['id'];
		// $items['questionType'] = $question['type'];
  //       $this->getHomeworkService()->createHomeworkItems($homeworkId, $items);

        $subQuestions = array();
        // if ($question['subCount'] > 0) {
        //     $subQuestions = $this->getQuestionService()->findQuestionsByParentId($question['id']);
        // } else {
        //     $subQuestions = array();
        // }

        $targets = $this->get('topxia.target_helper')->getTargets(array($question['target']));

        return $this->render('TopxiaWebBundle:CourseLessonManage:homework-item-picked.html.twig', array(
            'course'    => $course,
            'question' => $question,
            'subQuestions' => $subQuestions,
            'targets' => $targets,
            'type' => $question['type']
        ));
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

    private function getQuestionRanges($course, $includeCourse = false)
    {
        $lessons = $this->getCourseService()->getCourseLessons($course['id']);
        $ranges = array();

        if ($includeCourse == true) {
            $ranges["course-{$course['id']}"] = '本课程';
        }

        foreach ($lessons as  $lesson) {
            $ranges["course-{$lesson['courseId']}/lesson-{$lesson['id']}"] = "课时{$lesson['number']}： {$lesson['title']}";
        }

        return $ranges;
    }

    private function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

}