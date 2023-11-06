<?php

namespace AppBundle\Controller\Question;

use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseSetService;
use Biz\Question\Adapter\QuestionParseAdapter;
use Biz\Question\QuestionParseClient;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\User\Service\TokenService;
use Codeages\Biz\ItemBank\Item\ItemParser;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

class ManageController extends BaseController
{
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
                'errorHtml' => $this->renderView('question-manage/read-error.html.twig', ['error' => $result['error']]),
            ]);
        }
        if ('finished' == $result['status']) {
            try {
                $result['result'] = $this->replaceFormulaToImg($result['result']);
                $questions = $this->getQuestionParseAdapter()->adapt($result['result']);
                $questions = $this->getItemParser()->formatData($questions);
            } catch (\Exception $e) {
                return $this->createJsonResponse([
                    'status' => 'failed',
                    'errorHtml' => $this->renderView('question-manage/read-error.html.twig'),
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
        $postData = json_decode($request->getContent(), true);
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

    private function cacheQuestions($cacheFilePath, $questions)
    {
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile($cacheFilePath, json_encode($questions));
    }

    private function replaceFormulaToImg($text)
    {
        preg_match_all('/data-tex=\\\\"([^"]*)\\\\"/', html_entity_decode($text), $matches);
        $formulas = $matches[1] ?? [];
        if (empty($formulas)) {
            return $text;
        }
        $unescapeFormulas = str_replace('\\\\', '\\', $formulas);
        $imgs = [];
        foreach (array_chunk($unescapeFormulas, 100) as $formulaChunk) {
            $imgChunk = $this->getQuestionParseClient()->convertLatex2Img($formulaChunk);
            $imgs = array_merge($imgs, $imgChunk);
        }
        $replaceImgs = array_combine($formulas, $imgs);
        $replaceFunc = function ($match) use ($replaceImgs) {
            return "<img src=\\\"{$replaceImgs[html_entity_decode($match[1])]}\\\">";
        };

        return preg_replace_callback('/<span data-tex=\\\\"(.*?)\\\\".*?><\/span>/', $replaceFunc, $text);
    }

    protected function getQuestionParseClient()
    {
        return new QuestionParseClient();
    }

    protected function getQuestionParseAdapter()
    {
        return new QuestionParseAdapter();
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
