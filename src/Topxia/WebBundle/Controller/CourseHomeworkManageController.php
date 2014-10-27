<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;

class CourseHomeworkManageController extends BaseController
{
    public function createAction(Request $request, $courseId, $lessonId)
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

		return $this->render('TopxiaWebBundle:CourseHomeworkManage:homework-modal.html.twig', array(
            'course' => $course,
            'lesson' => $lesson,
        ));
	}

	public function editAction(Request $request,$courseId,$lessonId,$homeworkId)
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
        $homeworkItemsArray = array();

        foreach ($homeworkItems as $key => $homeworkItem) {
           if ($homeworkItem['parentId'] == "0") {
              $homeworkItemsArray[] = $homeworkItem;
           }
        }
        
        $homeworkItems = $homeworkItemsArray;
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

		return $this->render('TopxiaWebBundle:CourseHomeworkManage:homework-modal.html.twig', array(
            'course' => $course,
            'lesson' => $lesson,
            'homework' => $homework,
            'homeworkItems' => $homeworkItems,
            'questions' => $questions,
        ));
	}

	public function removeAction(Request $request,$courseId,$lessonId,$homeworkId)
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


        return $this->render('TopxiaWebBundle:CourseHomeworkManage:homework-items.html.twig', array(
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

        return $this->render('TopxiaWebBundle:CourseHomeworkManage:homework-items-picker.html.twig', array(
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

        $subQuestions = array();

        $targets = $this->get('topxia.target_helper')->getTargets(array($question['target']));

        return $this->render('TopxiaWebBundle:CourseHomeworkManage:homework-item-picked.html.twig', array(
            'course'    => $course,
            'question' => $question,
            'subQuestions' => $subQuestions,
            'targets' => $targets,
            'type' => $question['type']
        ));
	}


    public function previewAction(Request $request,$id,$courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            return $this->createMessageResponse('info','作业所属课程不存在！');
        }
        
        $homework = $this->getHomeworkService()->getHomework($id);

        if (empty($homework)) {
            throw $this->createNotFoundException();
        }

        $lesson = $this->getCourseService()->getCourseLesson($homework['courseId'], $homework['lessonId']);
        
        if (empty($lesson)) {
            return $this->createMessageResponse('info','作业所属课时不存在！');
        }

        $itemSet = $this->getHomeworkService()->getItemSetByHomeworkId($homework['id']);

        $homeworkResult = $this->getHomeworkService()->getHomeworkResultByHomeworkId($id);

        $user = $this->getUserService()->getUser($homeworkResult['userId']);

        return $this->render('TopxiaWebBundle:CourseHomeworkManage:preview.html.twig', array(
            'homework' => $homework,
            'homeworkResult' => $homeworkResult,
            'itemSet' => $itemSet,
            'course' => $course,
            'lesson' => $lesson,
            'user' => $user,
            'questionStatus' => 'previewing'
        ));    
    }

    public function homeworkResultAction (Request $request,$id)
    {
        $homeworkResult = $this->getHomeworkService()->getHomeworkResult($id);
        if (empty($homeworkResult)) {
            throw $this->createNotFoundException('作业不存在!');
        }

        if (in_array($homeworkResult['status'], array('doing'))){
            return $this->redirect($this->generateUrl('course_manage_lesson_homework_show', array('id' => $homeworkResult['id'])));
        }

        $homework = $this->getHomeworkService()->getHomework($homeworkResult['homeworkId']);

        if ($homeworkResult['userId'] != $this->getCurrentUser()->id) {
            throw $this->createAccessDeniedException('不可以访问其他学生的作业哦~');
        }

        $homeworkItems = $this->getHomeworkService()->findHomeworkItemsByHomeworkId($homeworkId);

        $questionIds = ArrayToolkit::column($homeworkItems,'questionId');
        $questions = $this->getQuestionService()->findQuestionsByIds($questionIds);

        $types = ArrayToolkit::column($questions,'type');
        $types = array_unique($types);
        $types = $this->sortType($types);

        $result = $this->getclassifyQuestionsAndCount($questions);

        $student = $this->getUserService()->getUser($testpaperResult['userId']);

        return $this->render('TopxiaWebBundle:CourseHomeworkManage:homework-result.html.twig', array(
            'course' => $course,
            'homework' => $homework,
            'items' => $homeworkItems,
            'questions' => $result['newQuetsions'],
            'typeQuestionsNum' => $result['typeQuestionsNum'],
            'types' => $types,
        ));
    }

    public function urgeAction (Request $request, $homeworkId, $userId)
    {
        $homework = $this->getHomeworkService()->getHomework($homeworkId);
        if (empty($homework)) {
            throw $this->createServiceException('作业不存在或者已被删除！');
        }

        $course = $this->getCourseService()->getCourse($homework['courseId']);
        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $homework['lessonId']);
        $student = $this->getUserService()->getUser($userId);
        $teacher = $this->getCurrentUser();

        if (empty($teacher)) {
            throw $this->createServiceException('用户不存在或者尚未登录，请先登录');
        }

        if (empty($student)) {
            throw $this->createServiceException('学生不存在或者已删除，请查验后再发送！');
        }

        if (empty($course)) {
            throw $this->createServiceException('课程不存在或已被删除！');
        }

        if (empty($lesson)) {
            throw $this->createServiceException('课时不存在或者已被删除！');
        }

        $message = $this->getUrgeMessageBody($course, $lesson);
        $this->getMessageService()->sendMessage($teacher['id'], $student['id'], $message);

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

        $conditions = array('courseId' => $homework['courseId']);
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchMemberCount($conditions)
            , 25
        );

        $students = $this->getCourseService()->searchMembers(
            $conditions, 
            array('createdTime', 'DESC'), 
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $studentUserIds = ArrayToolkit::column($students, 'userId');
        $users = $this->getUserService()->findUsersByIds($studentUserIds);
        $homeworkFinishedResults = $this->getHomeworkService()->findHomeworkResultsByCourseIdAndLessonIdAndStatus($course['id'], $lesson['id'],'finished');
        $homeworkReviewingResults = $this->getHomeworkService()->findHomeworkResultsByCourseIdAndLessonIdAndStatus($course['id'], $lesson['id'],'reviewing');
        $homeworkResults = array_merge($homeworkFinishedResults,$homeworkReviewingResults);
        $homeworkResults = ArrayToolkit::index($homeworkResults, 'userId');

        $committedCount = $this->getHomeworkService()->searchHomeworkResultsCount(array(
            'commitStatus' => 'committed',
            'courseId' => $course['id'],
            'lessonId' => $lesson['id']
        ));
        $uncommitCount = $this->getCourseService()->searchMemberCount($conditions) - $committedCount;
        if ($uncommitCount < 0 ) {
            $uncommitCount = 0;
        }
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
        if (!empty($homeworkResults)) {
            $students = $this->getHomeworkStudents($status, $students, $homeworkResults);
        } else {
            if ($status != 'uncommitted') {
                $students = array();
            }
        }
        if (empty($homework)) {
            $uncommitCount = 0;
        }
        return $this->render('TopxiaWebBundle:CourseHomeworkManage:homework-list.html.twig', array(
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

    public function teachingListAction(Request $request)
    {
        $status = $request->query->get('status', 'unchecked');

        $currentUser = $this->getCurrentUser();
        if (empty($currentUser)) {
            throw $this->createServiceException('用户不存在或者尚未登录，请先登录');
        }

        $homeworks = $this->getHomeworkService()->findHomeworksByCreatedUserId($currentUser['id']);

        $homeworkIds = ArrayToolkit::column($homeworks, 'id');
        $homeworkLessonIds = ArrayToolkit::column($homeworks, 'lessonId');
        $homeworks = ArrayToolkit::index($homeworks, 'courseId');
        $homeworkCourseIds = ArrayToolkit::column($homeworks, 'courseId');

        $courses = $this->getCourseService()->findCoursesByIds($homeworkCourseIds);
        $lessons = $this->getCourseService()->findLessonsByIds($homeworkLessonIds);
        $conditions = array('courseIds' => $homeworkCourseIds);

        // $memberCount = $this->getCourseService()->searchMemberCount($conditions);
        if (empty($homeworkCourseIds)) {
            $count = 0;
        }


        $homeworkReviewingResults = $this->getHomeworkService()->findHomeworkResultsByStatusAndCheckTeacherId('reviewing',$currentUser['id']);
        $homeworkFinishedResults = $this->getHomeworkService()->findHomeworkResultsByStatusAndCheckTeacherId('finished',$currentUser['id']);
        $homeworkResults = array_merge($homeworkReviewingResults,$homeworkFinishedResults);
        $paginator = new Paginator(
            $this->get('request'),
            count($homeworkResults)
            , 25
        );

        $students = $this->getCourseService()->searchMembers(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $studentUserIds = ArrayToolkit::column($students, 'userId');

        $users = $this->getUserService()->findUsersByIds($studentUserIds);
        $homeworkResults = ArrayToolkit::index($homeworkResults, 'userId');
        $committedCount = $this->getHomeworkService()->searchHomeworkResultsCount(array(
            'commitStatus' => 'committed',
            'checkTeacherId' => $currentUser['id']
        ));

        // $uncommitCount = $this->getCourseService()->searchMemberCount($conditions) - $committedCount;
        $reviewingCount = $this->getHomeworkService()->searchHomeworkResultsCount(array(
            'status' => 'reviewing',
            'checkTeacherId' => $currentUser['id']
        ));

        $finishedCount = $this->getHomeworkService()->searchHomeworkResultsCount(array(
            'status' => 'finished',
            'checkTeacherId' => $currentUser['id']
        ));

        if (!empty($homeworkResults)) {
            $students = $this->getHomeworkStudents($status, $students, $homeworkResults);
        } else {
            if ($status != 'uncommitted') {
                $students = array();
            }
        }

        if (empty($homeworks)) {
            $uncommitCount = 0;
        }

        return $this->render('TopxiaWebBundle:CourseHomeworkManage:teaching-list.html.twig', array(
            'status' => $status,
            'homeworks' => empty($homeworks) ? array() : $homeworks,
            'students' => $students,
            'users' => $users,
            'homeworkResults' => $homeworkResults,
            'paginator' => $paginator,
            'courses' => $courses,
            'lessons' => $lessons,
            // 'uncommitCount' => $uncommitCount,
            'reviewingCount' => $reviewingCount,
            'finishedCount' => $finishedCount
        ));
    }

    public function listAction(Request $request)
    {   
        $status = $request->query->get('status', 'finished');
        $currentUser = $this->getCurrentUser();

        $conditions = array(
            'status' => $status,
            'userId' => $currentUser['id']
        );
        $paginator = new Paginator(
            $this->get('request'),
            $this->getHomeworkService()->searchHomeworkResultsCount($conditions), 
            25
        );
        $homeworkResults = $this->getHomeworkService()->searchHomeworkResults(
            $conditions, 
            array('usedTime', 'DESC'), 
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $homeworkCourseIds = ArrayToolkit::column($homeworkResults, 'courseId');
        $homeworkLessonIds = ArrayToolkit::column($homeworkResults, 'lessonId');
        $courses = $this->getCourseService()->findCoursesByIds($homeworkCourseIds);
        $lessons = $this->getCourseService()->findLessonsByIds($homeworkLessonIds);

        return $this->render('TopxiaWebBundle:CourseHomeworkManage:list.html.twig',array(
            'status' => $status,
            'homeworkResults' => $homeworkResults,
            'courses' => $courses,
            'lessons' => $lessons,
            'user' => $currentUser,
            'paginator' => $paginator
        ));
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

    private function getHomeworkStudents($status, $students, $homeworkResults)
    {   
        if ($status == 'uncommitted') {
            foreach ($students as &$student) {
                foreach ($homeworkResults as $item) {
                    if ($item['status'] != 'doing' && $item['userId'] == $student['userId'] && $item['courseId'] == $student['courseId']) {
                        $student = null;
                    }
                }
            }
        } 

        if ($status == 'unchecked') {
            foreach ($students as &$student) {
                $key = false;
                foreach ($homeworkResults as $item) {
                    if ($item['status'] == 'reviewing' && $item['userId'] == $student['userId'] && $item['courseId'] == $student['courseId']) {
                        $key = true;
                    }
                }

                if ($key == true) {
                    continue;
                }
                $student = null;
            }
        }

        if ($status == 'finished') {
            foreach ($students as &$student) {
                $key = false;
                foreach ($homeworkResults as $item) {
                    if ($item['status'] == 'finished' && $item['userId'] == $student['userId'] && $item['courseId'] == $student['courseId']) {
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

    private function getUrgeMessageBody($course, $lesson)
    {   
        $urgeMessageBody = '你的作业还没提交<a href="'. $this->generateUrl('course_learn', array('id' =>$course['id'])) .'#lesson/'.$lesson['id'].'">（'.$course['title'].'第'.$lesson['number'].'课）</a>，请及时完成并提交。';

        return $urgeMessageBody;
    }
    
    private function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Course.HomeworkService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

    private function getMessageService()
    {
        return $this->getServiceKernel()->createService('User.MessageService');
    }
    
}