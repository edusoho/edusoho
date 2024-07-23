<?php

namespace AppBundle\Controller\Question;

use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseSetService;
use Biz\Question\Traits\QuestionAIAnalysisTrait;
use Biz\Question\Traits\QuestionImportTrait;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\User\Service\TokenService;
use Codeages\Biz\ItemBank\Item\ItemParser;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

class ManageController extends BaseController
{
    use QuestionImportTrait;
    use QuestionAIAnalysisTrait;

    public function indexAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        $sync = $request->query->get('sync');
        if ($courseSet['locked'] && empty($sync)) {
            return $this->redirectToRoute('course_set_manage_sync', [
                'id' => $id,
                'sideNav' => 'question',
            ]);
        }

        return $this->render('question-manage/index.html.twig', [
            'courseSet' => $courseSet,
        ]);
    }

    public function reEditAction(Request $request, $token)
    {
        return $this->forward('AppBundle:Question/QuestionParser:reEdit', [
            'request' => $request,
            'token' => $token,
            'type' => 'item',
        ]);
    }

    public function parseProgressAction($token)
    {
        $data = $this->getDataFromToken($token);
        $result = $this->getParseResult($data['jobId']);
        if ('failed' == $result['status']) {
            return $this->createJsonResponse([
                'status' => 'failed',
                'errorHtml' => $this->renderView('question-manage/read-error.html.twig', ['error' => $result['error'], 'questionBank' => ['id' => $data['questionBankId']]]),
            ]);
        }
        if ('finished' == $result['status']) {
            try {
                $questions = $this->getQuestionParseAdapter()->adapt($result['result']);
                $questions = $this->getItemParser()->formatData($questions);
                $questions = $this->wrapAIAnalysis($questions);
            } catch (\Exception $e) {
                return $this->createJsonResponse([
                    'status' => 'failed',
                    'errorHtml' => $this->renderView('question-manage/read-error.html.twig', ['questionBank' => ['id' => $data['questionBankId']]]),
                ]);
            }
            $this->cacheQuestions($data['cacheFilePath'], $questions);
        }

        return $this->createJsonResponse([
            'status' => $result['status'],
            'progress' => $result['progress'],
        ]);
    }

    public function saveImportQuestionsAction(Request $request, $token)
    {
        $data = $this->getDataFromToken($token);
        if (!$this->getQuestionBankService()->canManageBank($data['questionBankId'])) {
            $this->createNewException(QuestionBankException::FORBIDDEN_ACCESS_BANK());
        }
        $questionBank = $this->getQuestionBankService()->getQuestionBank($data['questionBankId']);
        $content = $this->replaceRemoteImgToLocalImg($request->getContent());
        $content = $this->replaceFormulaToLocalImg($content);
        $postData = json_decode($content, true);
        $this->getItemService()->importItems($postData['items'], $questionBank['itemBankId']);

        return $this->createJsonResponse(['goto' => $this->generateUrl('question_bank_manage_question_list', ['id' => $data['questionBankId']])]);
    }

    public function checkDuplicatedQuestionsAction(Request $request, $token)
    {
        $data = $this->getDataFromToken($token);
        if (!$this->getQuestionBankService()->canManageBank($data['questionBankId'])) {
            $this->createNewException(QuestionBankException::FORBIDDEN_ACCESS_BANK());
        }
        $questionBank = $this->getQuestionBankService()->getQuestionBank($data['questionBankId']);
        $fields = json_decode($request->getContent(), true);

        $duplicatedMaterialIds = $this->getItemService()->findDuplicatedMaterialIds($questionBank['itemBankId'], $fields['items']);

        return $this->createJsonResponse([
            'duplicatedIds' => $duplicatedMaterialIds,
        ]);
    }

    private function getDataFromToken($token)
    {
        $token = $this->getTokenService()->verifyToken('upload.course_private_file', $token);

        return $token['data'];
    }

    private function getParseResult($jobId)
    {
        $results = $this->getQuestionParseClient()->getJob($jobId);
        $results = array_column($results, null, 'no');

        return $results[$jobId];
    }

    private function wrapAIAnalysis($items)
    {
        foreach ($items as &$item) {
            foreach ($item['questions'] as &$question) {
                $question['aiAnalysisEnable'] = $this->canGenerateAIAnalysisForTeacher($question, $item);
            }
        }

        return $items;
    }

    private function cacheQuestions($cacheFilePath, $questions)
    {
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile($cacheFilePath, json_encode($questions));
    }

    /**
     * @return ItemParser
     */
    protected function getItemParser()
    {
        $biz = $this->getBiz();

        return $biz['item_parser'];
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->createService('ItemBank:Item:ItemService');
    }
}
