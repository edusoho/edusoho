<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

use Topxia\Service\Quiz\Impl\QuestionSerialize;

class DoTestController extends BaseController
{
	public function indexAction (Request $request, $testId)
	{
		$targetType = $request->query->get('targetType');
		$targetId = $request->query->get('targetId');

		$userId = $this->getCurrentUser()->id;

		$testPaper = $this->getTestService()->getTestPaper($testId);

		if (empty($testPaper)) {
			throw $this->createNotFoundException();
		}

		$testResult = $this->getTestService()->findTestPaperResultByTestIdAndUserId($testId, $userId);

		if (empty($testResult)) {

			if ($testPaper['status'] == 'draft') {
				return $this->createMessageResponse('info', '该试卷未发布，如有疑问请联系老师！');
			}
			if ($testPaper['status'] == 'closed') {
				return $this->createMessageResponse('info', '该试卷已关闭，如有疑问请联系老师！');
			}

			$testResult = $this->getTestService()->startTest($testId, $userId, $testPaper, array('type' => $targetType, 'id' => $targetId));
			return $this->redirect($this->generateUrl('course_manage_show_test', array('id' => $testResult['id'])));
		}

		if (in_array($testResult['status'], array('doing', 'paused'))) {
			return $this->redirect($this->generateUrl('course_manage_show_test', array('id' => $testResult['id'])));
		} else {
			return $this->redirect($this->generateUrl('course_manage_test_results', array('id' => $testResult['id'])));
		}
	}

	public function reDoAction (Request $request, $testId)
	{
		$targetType = $request->query->get('targetType');
		$targetId = $request->query->get('targetId');

		$userId = $this->getCurrentUser()->id;

		$testPaper = $this->getTestService()->getTestPaper($testId);

		if (empty($testPaper)) {
			throw $this->createNotFoundException();
		}

		$testResult = $this->getTestService()->findTestPaperResultByTestIdAndStatusAndUserId($testId, $userId, array('doing', 'paused'));

		if ($testResult && in_array($testResult['status'], array('doing', 'paused'))) {

			return $this->redirect($this->generateUrl('course_manage_show_test', array('id' => $testResult['id'])));
		}

		if ($testResult){
			$targetType = $testResult['targetType'];
			$targetId = $testResult['targetId'];
		}

		if ($testPaper['status'] == 'draft') {
			return $this->createMessageResponse('info', '该试卷未发布，如有疑问请联系老师！');
		}
		if ($testPaper['status'] == 'closed') {
			return $this->createMessageResponse('info', '该试卷已关闭，如有疑问请联系老师！');
		}

		$testResult = $this->getTestService()->startTest($testId, $userId, $testPaper, array('type' => $targetType, 'id' => $targetId));

		return $this->redirect($this->generateUrl('course_manage_show_test', array('id' => $testResult['id'])));
	}

	public function testPreviewAction (Request $request, $testId)
	{
		$paper = $this->getTestService()->getTestPaper($testId);

		if (!$teacherId = $this->getTestService()->canTeacherCheck($paper['id'])){
			throw createAccessDeniedException('无权预览试卷！');
		}

		$questions = $this->getTestService()->findQuestionsByTestId($testId);

		$questions = $this->formatQuestions($questions, $paper);

		$total = array();
		foreach ($paper['metas']['question_type_seq'] as $type) {
			$total[$type]['score'] = array_sum(ArrayToolkit::column($questions[$type], 'itemScore'));
			$total[$type]['number'] = count($questions[$type]);
		}

		return $this->render('TopxiaWebBundle:QuizQuestionTest:testpaper-show.html.twig', array(
			'questions' => $questions,
			'limitTime' => $paper['limitedTime'] * 60,
			'paper' => $paper,
			'id' => 0,
			'isPreview' => 'preview',
			'total' => $total
		));
	}

	public function showTestAction (Request $request, $id)
	{

		$testResult = $this->getTestService()->getTestPaperResult($id);
		if (!$testResult) {
			throw $this->createNotFoundException('试卷不存在!');
		}
		//权限！
		if ($testResult['userId'] != $this->getCurrentUser()->id) {
			throw $this->createAccessDeniedException('不可以访问其他学生的试卷哦~');
		}

		if (in_array($testResult['status'], array('reviewing', 'finished'))) {
			return $this->redirect($this->generateUrl('course_manage_test_results', array('id' => $testResult['id'])));
		}

		$paper = $this->getTestService()->getTestPaper($testResult['testId']);

		$questions = $this->getTestService()->testResults($id);

		$questions = $this->formatQuestions($questions, $paper);

		// $this->getTestService()->updatePaperResult($id, $testResult['remainTime']);

		$total = array();
		foreach ($paper['metas']['question_type_seq'] as $value) {
			$total[$value]['score'] = array_sum(ArrayToolkit::column($questions[$value], 'itemScore'));
			$total[$value]['number'] = count($questions[$value]);
		}

		$favorites = $this->getMyQuestionService()->findAllFavoriteQuestionsByUserId($testResult['userId']);

		return $this->render('TopxiaWebBundle:QuizQuestionTest:testpaper-show.html.twig', array(
			'questions' => $questions,
			'limitTime' => $testResult['limitedTime'] * 60,
			'paper' => $paper,
			'paperResult' => $testResult,
			'favorites' => ArrayToolkit::column($favorites, 'questionId'),
			'id' => $id,
			'total' => $total
		));
	}

	public function submitTestAction (Request $request, $id)
	{
		if ($request->getMethod() == 'POST') {
			$data = $request->request->all();
			$answers = array_key_exists('data', $data) ? $data['data'] : array();
			$usedTime = $data['usedTime'];

			$result = $this->getTestService()->submitTest($answers, $id);

			$this->getTestService()->updatePaperResult($id, $usedTime);

			return $this->createJsonResponse(true);
		}
	}

	public function finishTestAction (Request $request, $id)
	{
		if ($request->getMethod() == 'POST') {
			$data = $request->request->all();
			$answers = array_key_exists('data', $data) ? $data['data'] : array();
			$usedTime = $data['usedTime'];
			$user = $this->getCurrentUser();

			//提交变化的答案
			$results = $this->getTestService()->submitTest($answers, $id);

			//完成试卷，计算得分
			$testResults = $this->getTestService()->makeFinishTestResults($id);

			$testPaperResult = $this->getTestService()->getTestPaperResult($id);

			$testPaper = $this->getTestService()->getTestPaper($testPaperResult['testId']);
			//试卷信息记录
			$this->getTestService()->finishTest($id, $user['id'], $usedTime);

			if ($this->getTestService()->isExistsEssay($testResults)) {
				$user = $this->getCurrentUser();
	            $course = $this->getCourseService()->getCourse($testPaper['targetId']);

	            $userUrl = $this->generateUrl('user_show', array('id'=>$user['id']), true);
	            $teacherCheckUrl = $this->generateUrl('course_manage_test_teacher_check', array('id'=>$testPaperResult['id']), true);

	            foreach ($course['teacherIds'] as $receiverId) {
	                $result = $this->getNotificationService()->notify($receiverId, 'default', "【试卷已完成】 <a href='{$userUrl}' target='_blank'>{$user['nickname']}</a> 刚刚完成了 {$testPaperResult['paperName']} ，<a href='{$teacherCheckUrl}' target='_blank'>请点击批阅</a>");
	            }
			}

			// @todo refactor.
			if ($testPaperResult['targetType'] == 'lesson' and !empty($testPaperResult['targetId'])) {
				$lessons = $this->getCourseService()->findLessonsByIds(array($testPaperResult['targetId']));
				if (!empty($lessons[$testPaperResult['targetId']])) {
					$lesson = $lessons[$testPaperResult['targetId']];
					$this->getCourseService()->finishLearnLesson($lesson['courseId'], $lesson['id']);
				}
			}

			return $this->createJsonResponse(true);
			// return $this->redirect($this->generateUrl('course_manage_test_results', array('id' => $id)));
		}
	}

	public function testResultsAction (Request $request, $id)
	{

		$paperResult = $this->getTestService()->getTestPaperResult($id);
		if (!$paperResult) {
			throw $this->createNotFoundException('试卷不存在!');
		}
		//权限！
		if ($paperResult['userId'] != $this->getCurrentUser()->id) {
			throw $this->createAccessDeniedException('不可以访问其他学生的试卷哦~');
		}

		$paper = $this->getTestService()->getTestPaper($paperResult['testId']);

		$questions = $this->getTestService()->testResults($id);

		$accuracy = $this->makeAccuracy($questions);

		$questions = $this->formatQuestions($questions, $paper);

		$total = array();
		foreach ($paper['metas']['question_type_seq'] as $value) {
			$total[$value]['score'] = array_sum(ArrayToolkit::column($questions[$value], 'itemScore'));
			$total[$value]['number'] = count($questions[$value]);
		}

		$favorites = $this->getMyQuestionService()->findAllFavoriteQuestionsByUserId($paperResult['userId']);

		$student = $this->getUserService()->getUser($paperResult['userId']);

		return $this->render('TopxiaWebBundle:QuizQuestionTest:testpaper-result.html.twig', array(
			'questions' => $questions,
			'accuracy' => $accuracy,
			'paper' => $paper,
			'paperResult' => $paperResult,
			'favorites' => ArrayToolkit::column($favorites, 'questionId'),
			'id' => $id,
			'total' => $total,
			'student' => $student
		));
	}

	public function testSuspendAction (Request $request, $id)
	{
		$paperResult = $this->getTestService()->getTestPaperResult($id);
		if (!$paperResult) {
			throw $this->createNotFoundException('试卷不存在!');
		}
		//权限！
		if ($paperResult['userId'] != $this->getCurrentUser()->id) {
			throw $this->createAccessDeniedException('不可以访问其他学生的试卷哦~');
		}

		if ($request->getMethod() == 'POST') {
			$data = $request->request->all();
			$answers = array_key_exists('data', $data) ? $data['data'] : array();
			$usedTime = $data['usedTime'];

			$results = $this->getTestService()->submitTest($answers, $id);

			$this->getTestService()->updatePaperResult($id, $usedTime);

			return $this->createJsonResponse(true);
		}

	}

	public function testPauseAction(Request $request)
	{
		return $this->render('TopxiaWebBundle:QuizQuestionTest:do-test-pause-modal.html.twig'); 
	}

	public function testFinishCheckAction(Request $request)
	{
		return $this->render('TopxiaWebBundle:QuizQuestionTest:testpaper-finished-check-modal.html.twig'); 
	}

	public function teacherCheckAction (Request $request, $id)
	{
		//身份校验?

		$paperResult = $this->getTestService()->getTestPaperResult($id);

		$paper = $this->getTestService()->getTestPaper($paperResult['testId']);


		if (!$teacherId = $this->getTestService()->canTeacherCheck($paper['id'])){
			throw createAccessDeniedException('无权批阅试卷！');
		}

		if ($paperResult['status'] != 'reviewing') {
			return $this->createMessageResponse('info', '只有待批阅状态的试卷，才能批阅！');
		}


		if ($request->getMethod() == 'POST') {
			$form = $request->request->all();

			$paperResult = $this->getTestService()->makeTeacherFinishTest($id, $paper['id'], $teacherId, $form);

			$user = $this->getCurrentUser();

	        $userUrl = $this->generateUrl('user_show', array('id'=>$user['id']), true);
	        $testPaperResultUrl = $this->generateUrl('course_manage_test_results', array('id'=>$paperResult['id']), true);

	        $result = $this->getNotificationService()->notify($paperResult['userId'], 'default', "【试卷已批阅】 <a href='{$userUrl}' target='_blank'>{$user['nickname']}</a> 刚刚批阅了 {$paperResult['paperName']} ，<a href='{$testPaperResultUrl}' target='_blank'>请点击查看结果</a>");
			
			return $this->createJsonResponse(true);
		}


		$questions = $this->getTestService()->testResults($id);

		$accuracy = $this->makeAccuracy($questions);

		$questions = $this->formatQuestions($questions, $paper);

		$total = array();
		foreach ($paper['metas']['question_type_seq'] as $value) {
			$total[$value]['score'] = array_sum(ArrayToolkit::column($questions[$value], 'itemScore'));
			$total[$value]['number'] = count($questions[$value]);
		}

		$types =array();
		if (in_array('essay', $paper['metas']['question_type_seq'])){
			array_push($types, 'essay');
		}
		if (in_array('material', $paper['metas']['question_type_seq'])){
			
			foreach ($questions['material'] as $key => $value) {

				$questionTypes = ArrayToolkit::index(empty($value['questions']) ? array() : $value['questions'], 'type');

				if(array_key_exists('essay', $questionTypes)){
					array_push($types, 'material');
				}
			}
		}

		$student = $this->getUserService()->getUser($paperResult['userId']);

		return $this->render('TopxiaWebBundle:QuizQuestionTest:testpaper-review.html.twig', array(
			'questions' => $questions,
			'accuracy' => $accuracy,
			'paper' => $paper,
			'paperResult' => $paperResult,
			'id' => $id,
			'total' => $total,
			'types' => $types,
			'student' => $student
		));
	}

	public function teacherCheckInCourseAction (Request $request, $id, $status)
	{
		$user = $this->getCurrentUser();

		$course = $this->getCourseService()->tryManageCourse($id);

		$papers = $this->getTestService()->findAllTestPapersByTarget('course', $id);

		$paperIds = ArrayToolkit::column($papers, 'id');

		$paginator = new Paginator(
            $request,
            $this->getMyQuestionService()->findTestPaperResultCountByStatusAndTestIds($paperIds, $status),
            10
        );

		$paperResults = $this->getMyQuestionService()->findTestPaperResultsByStatusAndTestIds(
            $paperIds,
            $status,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($paperResults, 'userId'));

        $teacherIds = ArrayToolkit::column($paperResults, 'checkTeacherId');

        $teachers = $this->getUserService()->findUsersByIds($teacherIds);


        return $this->render('TopxiaWebBundle:MyQuiz:list-course-test-paper.html.twig', array(
        	'status' => $status,
			'testPapers' => ArrayToolkit::index($papers, 'id'),
            'paperResults' => ArrayToolkit::index($paperResults, 'id'),
            'course' => $course,
            'users' => $users,
            'teachers' => ArrayToolkit::index($teachers, 'id'),
            'paginator' => $paginator
        ));
	}

	public function openTestPaperAction (Request $request, $id)
	{
		$testPaper = $this->getTestService()->getTestPaper($id);
		if (empty($testPaper)){
			throw $this->createNotFoundException();
		}
		$testPaper = $this->getTestService()->publicTestPaper($id, 'open');

		$user = $this->getUserService()->getUser($testPaper['updatedUserId']);

		$course = $this->getCourseService()->getCourse($testPaper['targetId']);

		return $this->render('TopxiaWebBundle:QuizQuestionTest:tr.html.twig', array(
            'item' => $testPaper,
            'user' => $user,
            'course' => $course
        ));
	}

	public function closeTestPaperAction (Request $request, $id)
	{
		$testPaper = $this->getTestService()->getTestPaper($id);
		if (empty($testPaper)){
			throw $this->createNotFoundException();
		}
		$testPaper = $this->getTestService()->publicTestPaper($id, 'closed');

		$user = $this->getUserService()->getUser($testPaper['updatedUserId']);

		$course = $this->getCourseService()->getCourse($testPaper['targetId']);

		return $this->render('TopxiaWebBundle:QuizQuestionTest:tr.html.twig', array(
            'item' => $testPaper,
            'user' => $user,
            'course' => $course
        ));
	}

	private function makeAccuracy ($questions)
    {
        $accuracyResult = array(
			'right' => 0,
			'wrong' => 0,
			'noAnswer' => 0,
			'all' => 0,
			'score' => 0,
			'totalScore' => 0
		);
		$accuracy = array(
			'single_choice' => $accuracyResult,
			'choice' => $accuracyResult,
			'determine' => $accuracyResult,
			'fill' => $accuracyResult,
			'essay' => $accuracyResult,
			'material' => $accuracyResult
		);

		foreach ($questions as $value) {

			if ($value['type'] == 'material'){
				if (!array_key_exists('questions', $value)){
					continue;
				}
				foreach ($value['questions'] as $key => $v) {

					if ($v['type'] == 'essay'){
						$accuracy['material']['hasEssay'] = true;
					}

					$accuracy['material']['score'] += $v['testResult']['score'];
					$accuracy['material']['totalScore'] += $v['itemScore'];

					$accuracy['material']['all']++;
					if ($v['testResult']['status'] == 'right'){
						$accuracy['material']['right']++;
					}
					if ($v['testResult']['status'] == 'wrong'){
						$accuracy['material']['wrong']++;
					}
					if ($v['testResult']['status'] == 'noAnswer'){
						$accuracy['material']['noAnswer']++;
					}
				}
			} else {

				$accuracy[$value['type']]['score'] += $value['testResult']['score'];
				$accuracy[$value['type']]['totalScore'] += $value['itemScore'];

				$accuracy[$value['type']]['all']++;
				if ($value['testResult']['status'] == 'right'){
					$accuracy[$value['type']]['right']++;
				}
				if ($value['testResult']['status'] == 'wrong'){
					$accuracy[$value['type']]['wrong']++;
				}
				if ($value['testResult']['status'] == 'noAnswer'){
					$accuracy[$value['type']]['noAnswer']++;
				}

			}
		}

        return $accuracy;
    }

	private function formatQuestions ($questions, $paper)
	{
		$formatQuestions = array();
		$number = 0;

		$questions = ArrayToolkit::index($questions, 'seq');
		ksort($questions);

		foreach ($questions as $key => $value) {

			if(in_array($value['type'], array('single_choice', 'choice'))) {

				ksort($value['choices']);
				$value['choices'] = array_values($value['choices']);
				foreach ($value['choices'] as $k => $v) {
					$v['choiceIndex'] = chr($k+65);
					$value['choices'][$k] = $v;
				}
			}

			if ($value['type'] == 'material') {
				if(array_key_exists('questions', $value)){
					$value['questions'] = $this->formatQuestions($value['questions'], $paper);
					$number += $value['questions']['number'];
					unset($value['questions']['number']);
				}
			} else {
				$number++;
			}

			if ($value['targetId'] != 0) {
				$formatQuestions[$value['type']][$key] = $value;


				$diff = array_diff($paper['metas']['question_type_seq'], array_keys($formatQuestions));
				foreach ($diff as $type) {
					$formatQuestions[$type] = array();
				}
				
			} else {
				$formatQuestions[$key] = $value;
			}
		}



		$formatQuestions['number'] = $number;

		return $formatQuestions;
	}

   	private function getQuestionService()
   	{
   		return $this->getServiceKernel()->createService('Quiz.QuestionService');
   	}

   	private function getTestService()
   	{
   		return $this->getServiceKernel()->createService('Quiz.TestService');
   	}

   	private function getMyQuestionService ()
	{
		return $this->getServiceKernel()->createService('Quiz.MyQuestionService');
	}

	private function getCourseService ()
	{
		return $this->getServiceKernel()->createService('Course.CourseService');
	}

	protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    private function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

}