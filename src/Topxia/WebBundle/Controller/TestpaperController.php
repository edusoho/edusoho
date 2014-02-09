<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class TestpaperController extends BaseController
{

    public function doTestpaperAction (Request $request, $testId)
    {
        $targetType = $request->query->get('targetType');
        $targetId = $request->query->get('targetId');

        $userId = $this->getCurrentUser()->id;

        $testpaper = $this->getTestpaperService()->getTestPaper($testId);

        if (empty($testpaper)) {
            throw $this->createNotFoundException();
        }

        $testpaperResult = $this->getTestpaperService()->findTestpaperResultsByTestpaperIdAndUserId($testId, $userId);

        if (empty($testpaperResult)) {

            if ($testpaper['status'] == 'draft') {
                return $this->createMessageResponse('info', '该试卷未发布，如有疑问请联系老师！');
            }
            if ($testpaper['status'] == 'closed') {
                return $this->createMessageResponse('info', '该试卷已关闭，如有疑问请联系老师！');
            }

            $testpaperResult = $this->getTestpaperService()->startTestpaper($testId, array('type' => $targetType, 'id' => $targetId));

            return $this->redirect($this->generateUrl('course_manage_show_test', array('id' => $testpaperResult['id'])));
        }

        if (in_array($testpaperResult['status'], array('doing', 'paused'))) {
            return $this->redirect($this->generateUrl('course_manage_show_test', array('id' => $testpaperResult['id'])));
        } else {
            return $this->redirect($this->generateUrl('course_manage_test_results', array('id' => $testpaperResult['id'])));
        }
    }

    public function reDoTestpaperAction (Request $request, $testId)
    {
        $targetType = $request->query->get('targetType');
        $targetId = $request->query->get('targetId');

        $userId = $this->getCurrentUser()->id;

        $testpaper = $this->getTestpaperService()->getTestPaper($testId);

        if (empty($testPaper)) {
            throw $this->createNotFoundException();
        }

        $testResult = $this->getTestpaperService()->findTestPaperResultByTestIdAndStatusAndUserId($testId, $userId, array('doing', 'paused'));

        if ($testResult) {
            return $this->redirect($this->generateUrl('course_manage_show_test', array('id' => $testResult['id'])));
        }

        if ($testPaper['status'] == 'draft') {
            return $this->createMessageResponse('info', '该试卷未发布，如有疑问请联系老师！');
        }
        if ($testPaper['status'] == 'closed') {
            return $this->createMessageResponse('info', '该试卷已关闭，如有疑问请联系老师！');
        }

        $testResult = $this->getTestpaperService()->startTestpaper($testId, array('type' => $targetType, 'id' => $targetId));

        return $this->redirect($this->generateUrl('course_manage_show_test', array('id' => $testResult['id'])));
    }

    public function previewTestAction (Request $request, $testId)
    {
        $testpaper = $this->getTestpaperService()->getTestPaper($testId);

        if (!$teacherId = $this->getTestpaperService()->canTeacherCheck($testpaper['id'])){
            throw createAccessDeniedException('无权预览试卷！');
        }

        $items = $this->getTestpaperService()->previewTestpaper($testId);

        $total = array();
        foreach ($testpaper['metas']['question_type_seq'] as $type) {
            if (empty($items[$type])) {
                $total[$type]['score'] = 0;
                $total[$type]['number'] = 0;
            } else {
                $total[$type]['score'] = array_sum(ArrayToolkit::column($items[$type], 'score'));
                $total[$type]['number'] = count($items[$type]);
            }
        }

        return $this->render('TopxiaWebBundle:QuizQuestionTest:testpaper-show.html.twig', array(
            'items' => $items,
            'limitTime' => $testpaper['limitedTime'] * 60,
            'paper' => $testpaper,
            'id' => 0,
            'isPreview' => 'preview',
            'total' => $total
        ));
    }

    public function showTestAction (Request $request, $id)
    {
        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($id);
        if (!$testpaperResult) {
            throw $this->createNotFoundException('试卷不存在!');
        }
        if ($testpaperResult['userId'] != $this->getCurrentUser()->id) {
            throw $this->createAccessDeniedException('不可以访问其他学生的试卷哦~');
        }
        if (in_array($testpaperResult['status'], array('reviewing', 'finished'))) {
            return $this->redirect($this->generateUrl('course_manage_test_results', array('id' => $testpaperResult['id'])));
        }

        $testpaper = $this->getTestpaperService()->getTestPaper($testpaperResult['testId']);

        $items = $this->getTestpaperService()->showTestpaper($id);

        $total = array();
        foreach ($testpaper['metas']['question_type_seq'] as $type) {
            if (empty($items[$type])) {
                $total[$type]['score'] = 0;
                $total[$type]['number'] = 0;
            } else {
                $total[$type]['score'] = array_sum(ArrayToolkit::column($items[$type], 'score'));
                $total[$type]['number'] = count($items[$type]);
            }
        }

        $favorites = $this->getQuestionService()->findAllFavoriteQuestionsByUserId($testpaperResult['userId']);

        return $this->render('TopxiaWebBundle:QuizQuestionTest:testpaper-show.html.twig', array(
            'items' => $items,
            'limitTime' => $testpaperResult['limitedTime'] * 60,
            'paper' => $testpaper,
            'paperResult' => $testpaperResult,
            'favorites' => ArrayToolkit::column($favorites, 'questionId'),
            'id' => $id,
            'total' => $total
        ));
    }




    private function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper.TestpaperService');
    }

    private function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }































    public function userResultJsonAction(Request $request, $id)
    {
        $user = $this->getCurrentUser()->id;
        if (empty($user)) {
            return $this->createJsonResponse(array('error' => '您尚未登录系统或登录已超时，请先登录。'));
        }

        $testPaper = $this->getTestpaperService()->getTestPaper($id);
        if (empty($testPaper)) {
            return $this->createJsonResponse(array('error' => '试卷已删除，请联系管理员。'));
        }

        $testResult = $this->getTestpaperService()->findTestPaperResultByTestIdAndUserId( $id, $user);

        if (empty($testResult)) {
            return $this->createJsonResponse(array('status' => 'nodo'));
        }

        return $this->createJsonResponse(array('status' => $testResult['status'], 'resultId' => $testResult['id']));
    }

    // private function getTestpaperService()
    // {
    //     return $this -> getServiceKernel()->createService('Quiz.TestService');
    // }

}