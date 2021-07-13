<?php

namespace AppBundle\Controller\My;

use ApiBundle\Api\ApiRequest;
use AppBundle\Controller\BaseController;
use Biz\Common\CommonException;
use Biz\User\UserException;
use Biz\WrongBook\Service\WrongQuestionService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class WrongQuestionBookController extends BaseController
{
    public function indexAction(Request $request)
    {
        return $this->render('my/learning/wrong-question-book/index.html.twig');
    }

    public function detailAction(Request $request)
    {
        return $this->render('my/learning/wrong-question-book/detail.html.twig');
    }

    public function practiseAction(Request $request, $poolId, $recordId)
    {
        return $this->render('my/learning/wrong-question-book/practise.html.twig', [
            'poolId' => $poolId,
            'recordId' => $recordId,
        ]);
    }

    public function practiseRedirectAction(Request $request, $poolId)
    {
        $apiRequest = new ApiRequest("/api/wrong_book/{$poolId}/start_answer", 'POST');
        $result = $this->container->get('api_resource_kernel')->handleApiRequest($apiRequest);
        $record = $result['answer_record'];

        return $this->redirect($this->generateUrl('wrong_question_book_practise', ['poolId' => $poolId, 'recordId' => $record['id']]));
    }

    public function startDoAction(Request $request, $poolId, $recordId)
    {
        return $this->forward('AppBundle:AnswerEngine/AnswerEngine:do', [
            'answerRecordId' => $recordId,
            'submitGotoUrl' => $this->generateUrl('wrong_question_book_practise_show_result', ['poolId' => $poolId, 'recordId' => $recordId]),
            'saveGotoUrl' => false,
            'showHeader' => 1,
            'showSaveProgressBtn' => 0,
        ]);
    }

    public function showResultAction(Request $request, $poolId, $recordId)
    {
        if (!$this->canLookAnswerRecord($recordId)) {
            $this->createNewException(CommonException::FORBIDDEN_DRAG_CAPTCHA_ERROR());
        }

        $pool = $this->getWrongQuestionService()->getPool($poolId);

        $answerRecord = $this->getAnswerRecordService()->get($recordId);
        $assessment = $this->getAssessmentService()->getAssessment($answerRecord['assessment_id']);

        return $this->render('my/learning/wrong-question-book/result.html.twig', [
            'answerRecordId' => $recordId,
            'assessment' => $assessment,
            'showHeader' => 1,
            'restartUrl' => '',
            'showDoAgainBtn' => 0,
            'submitReturnUrl' => $this->generateUrl('my_wrong_question_book_target_detail', [], UrlGeneratorInterface::ABSOLUTE_URL)."#/target_type/{$pool['target_type']}/target_id/{$poolId}",
        ]);
    }

    protected function canLookAnswerRecord($answerRecordId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);

        if (!$answerRecord) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        if ('doing' === $answerRecord['status'] && ($answerRecord['user_id'] != $user['id'])) {
            $this->createNewException(CommonException::FORBIDDEN_DRAG_CAPTCHA_ERROR());
        }

        return true;
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->createService('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->createService('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return WrongQuestionService
     */
    protected function getWrongQuestionService()
    {
        return $this->createService('WrongBook:WrongQuestionService');
    }
}
