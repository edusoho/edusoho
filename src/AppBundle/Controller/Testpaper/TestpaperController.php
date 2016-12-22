<?php
namespace AppBundle\Controller\Testpaper;

use Topxia\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Exception\AccessDeniedException;

class TestpaperController extends BaseController
{
    public function doTestpaperAction(Request $request, $testId, $lessonId)
    {
        $user = $this->getUser();

        $testpaper = $this->getTestpaperService()->getTestpaper($testId);

        if (empty($testpaper)) {
            throw $this->createResourceNotFoundException('testpaper', $testId);
        }

        if ($testpaper['status'] == 'draft') {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('该试卷未发布，如有疑问请联系老师！'));
        }

        if ($testpaper['status'] == 'closed') {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('该试卷已关闭，如有疑问请联系老师！'));
        }

        $testpaperResult = $this->getTestpaperService()->startTestpaper($testpaper['id'], $lessonId);

        if (in_array($testpaperResult['status'], array('doing', 'paused'))) {
            return $this->redirect($this->generateUrl('testpaper_show', array('resultId' => $testpaperResult['id'])));
        } else {
            return $this->redirect($this->generateUrl('testpaper_result_show', array('resultId' => $testpaperResult['id'])));
        }
    }

    public function doTestAction(Request $request, $resultId)
    {
        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($resultId);

        if (in_array($testpaperResult['status'], array('reviewing', 'finished'))) {
            return $this->redirect($this->generateUrl('testpaper_result_show', array('resultId' => $testpaperResult['id'])));
        }

        //$canLookTestpaper = $this->getTestpaperService()->canLookTestpaper($testpaperResult['id']);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);

        $questions = $this->getTestpaperService()->showTestpaperItems($testpaper['id'], $testpaperResult['id']);

        $total = $this->getTestpaperService()->countQuestionTypes($testpaper, $questions);

        $favorites = $this->getQuestionService()->findUserFavoriteQuestions($testpaperResult['userId']);

        $activity          = $this->getActivityService()->getActivity($testpaperResult['lessonId']);
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);

        if ($testpaperActivity['testMode'] == 'realTime') {
            $testpaperResult['usedTime'] = time() - $activity['startTime'];
        }

        $attachments = $this->getTestpaperService()->findAttachments($testpaper['id']);
        $limitedTime = $testpaperResult['limitedTime'] ? $testpaperResult['limitedTime'] : $testpaperActivity['limitedTime'];

        return $this->render('testpaper/start-do-show.html.twig', array(
            'questions'         => $questions,
            'limitedTime'       => $limitedTime,
            'paper'             => $testpaper,
            'paperResult'       => $testpaperResult,
            'activity'          => $activity,
            'testpaperActivity' => $testpaperActivity,
            'favorites'         => ArrayToolkit::column($favorites, 'questionId'),
            'total'             => $total,
            'attachments'       => $attachments,
            'questionTypes'     => $this->getCheckedQuestionType($testpaper),
            'showTypeBar'       => 1,
            'showHeader'        => 0
        ));
    }

    public function showResultAction(Request $request, $resultId)
    {
        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($resultId);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);

        if (!$testpaper) {
            throw $this->createResourceNotFoundException('testpaper', $testpaperResult['testId']);
        }

        if (in_array($testpaperResult['status'], array('doing', 'paused'))) {
            return $this->redirect($this->generateUrl('course_manage_show_test', array('id' => $testpaperResult['id'])));
        }

        /*$canLookTestpaper = $this->getTestpaperService()->canLookTestpaper($testpaperResult['id']);

        if (!$canLookTestpaper) {
        throw new AccessDeniedException($this->getServiceKernel()->trans('无权查看试卷！'));
        }*/

        $builder   = $this->getTestpaperService()->getTestpaperBuilder($testpaper['type']);
        $questions = $builder->showTestItems($testpaper['id'], $testpaperResult['id']);

        $accuracy = $this->getTestpaperService()->makeAccuracy($testpaperResult['id']);

        $total = $this->makeTestpaperTotal($testpaper, $questions);

        $favorites = $this->getQuestionService()->findUserFavoriteQuestions($testpaperResult['userId']);

        $student = $this->getUserService()->getUser($testpaperResult['userId']);

        $attachments = $this->getTestpaperService()->findAttachments($testpaper['id']);

        return $this->render('testpaper/result.html.twig', array(
            'questions'     => $questions,
            'accuracy'      => $accuracy,
            'paper'         => $testpaper,
            'paperResult'   => $testpaperResult,
            'favorites'     => ArrayToolkit::column($favorites, 'questionId'),
            'total'         => $total,
            'student'       => $student,
            'source'        => $request->query->get('source', 'course'),
            'attachments'   => $attachments,
            'questionTypes' => $this->getCheckedQuestionType($testpaper),
            'limitedTime'   => 0
        ));
    }

    public function reDoTestpaperAction(Request $request, $targetType, $targetId, $testId)
    {
        $userId = $this->getUser()->id;

        $testpaper = $this->getTestpaperService()->getTestpaper($testId);

        if (empty($testpaper)) {
            throw $this->createResourceNotFoundException('testpaper', $testId);
        }

        $testResult = $this->getTestpaperService()->findTestpaperResultsByTestIdAndStatusAndUserId($testId, $userId, array('doing', 'paused'));

        if ($testResult) {
            return $this->redirect($this->generateUrl('course_manage_show_test', array('id' => $testResult['id'])));
        }

        if ($testpaper['status'] == 'draft') {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('该试卷未发布，如有疑问请联系老师！'));
        }

        if ($testpaper['status'] == 'closed') {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('该试卷已关闭，如有疑问请联系老师！'));
        }

        $testResult = $this->getTestpaperService()->findTestpaperResultsByTestIdAndStatusAndUserId($testId, $userId, array('reviewing'));

        if (!empty($testResult)) {
            throw new AccessDeniedException($this->getServiceKernel()->trans('试卷还在批阅中'));
        }

        $testResult = $this->getTestpaperService()->startTestpaper($testId, array('type' => $targetType, 'id' => $targetId));

        return $this->redirect($this->generateUrl('course_manage_show_test', array('id' => $testResult['id'])));
    }

    public function realTimeCheckAction(Request $request)
    {
        $testId = $request->query->get('value');

        $testPaper = $this->getTestpaperService()->getTestpaper($testId);

        if (empty($testPaper)) {
            $response = array('success' => false, 'message' => $this->getServiceKernel()->trans('试卷不存在'));
            return $this->createJsonResponse($response);
        }

        if ($testPaper['limitedTime'] == 0) {
            $response = array('success' => false, 'message' => $this->getServiceKernel()->trans('该试卷考试时间未限制,请选择其他限制时长的试卷'));
        } else {
            $response = array('success' => true, 'message' => '');
        }

        return $this->createJsonResponse($response);
    }

    protected function getCheckedQuestionType($testpaper)
    {
        $questionTypes = array();
        foreach ($testpaper['metas']['counts'] as $type => $count) {
            if ($count > 0) {
                $questionTypes[] = $type;
            }
        }

        return $questionTypes;
    }

    public function testSuspendAction(Request $request, $resultId)
    {
        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($resultId);

        if (!$testpaperResult) {
            throw $this->createResourceNotFoundException('testpaperResult', $resultId);
        }

        $user = $this->getUser();
        if ($testpaperResult['userId'] != $user['id']) {
            throw new AccessDeniedException($this->getServiceKernel()->trans('不可以访问其他学生的试卷哦~'));
        }

        if ($request->getMethod() == 'POST') {
            $data     = $request->request->all();
            $answers  = !empty($data['data']) ? $data['data'] : array();
            $usedTime = $data['usedTime'];

            $results = $this->getTestpaperService()->submitAnswers($testpaperResult['id'], $answers);

            $this->getTestpaperService()->updateTestpaperResult($testpaperResult['id'], array('usedTime' => $usedTime));

            return $this->createJsonResponse(true);
        }
    }

    public function submitTestAction(Request $request, $resultId)
    {
        if ($request->getMethod() == 'POST') {
            $data     = $request->request->all();
            $answers  = array_key_exists('data', $data) ? $data['data'] : array();
            $usedTime = $data['usedTime'];

            $results = $this->getTestpaperService()->submitTestpaperAnswer($id, $answers);

            $this->getTestpaperService()->updateTestpaperResult($resultId, $usedTime);

            return $this->createJsonResponse(true);
        }
    }

    public function finishTestAction(Request $request, $resultId)
    {
        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($resultId);

        if (!empty($testpaperResult) && !in_array($testpaperResult['status'], array('doing', 'paused'))) {
            return $this->createJsonResponse(array('result' => false, 'message' => '试卷已提交，不能再修改答案！'));
        }

        if ($request->getMethod() == 'POST') {
            $activity          = $this->getActivityService()->getActivity($testpaperResult['lessonId']);
            $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);

            if ($activity['endTime'] && time() > $activity['endTime']) {
                return $this->createJsonResponse(array('result' => false, 'message' => '考试时间已过，不能再提交！'));
            }

            $formData = $request->request->all();

            $paperResult = $this->getTestpaperService()->finishTest($testpaperResult['id'], $formData);

            if ($testpaperActivity['finishCondition']['type'] == 'submit') {
                $response = array('result' => true, 'message' => '');
            } elseif ($testpaperActivity['finishCondition']['type'] == 'score' && $paperResult['status'] == 'finished' && $paperResult['score'] > $testpaperActivity['finishCondition']['finishScore']) {
                $response = array('result' => true, 'message' => '');
            } else {
                $response = array('result' => false, 'message' => '');
            }

            return $this->createJsonResponse($response);
        }
    }

    public function pauseTestAction(Request $request)
    {
        return $this->render('TopxiaWebBundle:QuizQuestionTest:do-test-pause-modal.html.twig');
    }

    protected function makeTestpaperTotal($testpaper, $items)
    {
        $total = array();

        foreach ($testpaper['metas']['counts'] as $type => $count) {
            if (empty($items[$type])) {
                $total[$type]['score']     = 0;
                $total[$type]['number']    = 0;
                $total[$type]['missScore'] = 0;
            } else {
                $total[$type]['score']  = array_sum(ArrayToolkit::column($items[$type], 'score'));
                $total[$type]['number'] = count($items[$type]);

                if (array_key_exists('missScore', $testpaper['metas']) && array_key_exists($type, $testpaper['metas']['missScore'])) {
                    $total[$type]['missScore'] = $testpaper['metas']['missScore'][$type];
                } else {
                    $total[$type]['missScore'] = 0;
                }
            }
        }

        return $total;
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    protected function getTestpaperActivityService()
    {
        return $this->createService('Activity:TestpaperActivityService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
