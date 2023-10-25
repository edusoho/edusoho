<?php

namespace AppBundle\Controller\Question;

use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Question\Adapter\QuestionParseAdapter;
use Biz\Question\QuestionParseClient;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\Task\Service\TaskService;
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

    public function showTasksAction(Request $request, $courseSetId)
    {
        $courseId = $request->request->get('courseId', 0);
        if (empty($courseId)) {
            return $this->createJsonResponse([]);
        }

        $this->getCourseService()->tryManageCourse($courseId);

        $courseTasks = $this->getTaskService()->findTasksByCourseId($courseId);

        return $this->createJsonResponse($courseTasks);
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
        $token = $this->getTokenService()->verifyToken('upload.course_private_file', $token);
        $data = $token['data'];
        $results = $this->getQuestionParseClient()->getJob($data['jobId']);
        $results = array_column($results, null, 'no');
        $result = $results[$data['jobId']];
        if ('finished' == $result['status']) {
            $questions = $this->getQuestionParseAdapter()->adapt($result['result']);
            $questions = $this->getTransferImg($questions);
            $questions = $this->getItemParser()->formatData($questions);
            $fileSystem = new Filesystem();
            $fileSystem->dumpFile($data['cacheFilePath'], json_encode($questions));
        }
        if ('failed' == $result['status']) {
            return $this->createJsonResponse([
                'status' => $result['status'],
                'errorHtml' => $this->renderView('question-manage/read-error.html.twig'),
            ]);
        }

        return $this->createJsonResponse([
            'status' => $result['status'],
            'progress' => $result['progress'],
        ]);
    }

    public function saveImportQuestionsAction(Request $request, $token)
    {
        $token = $this->getTokenService()->verifyToken('upload.course_private_file', $token);
        $data = $token['data'];
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
        $token = $this->getTokenService()->verifyToken('upload.course_private_file', $token);
        $data = $token['data'];
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

    protected function getTransferImg($questions)
    {
        $formulas = [];
        foreach ($questions as &$question) {
            $formulas = array_merge($formulas, $this->getFormulasFromText(html_entity_decode($question['stem'])));
            if (isset($question['options'])) {
                foreach ($question['options'] as &$option) {
                    $formulas = array_merge($formulas, $this->getFormulasFromText(html_entity_decode($option)));
                }
            }
        }

        if (count($formulas) > 100) {
            $dataResults = [];
            $formulas = array_chunk($formulas, 100);
            foreach ($formulas as $formula) {
                $results = $this->getQuestionParseClient()->convertLatex2Img($formula);
                $dataResults = array_merge($dataResults, $results);
            }

            return $this->replaceQuestions($formulas, $dataResults, $questions);
        }

        $results = $this->getQuestionParseClient()->convertLatex2Img($formulas);

        return $this->replaceQuestions($formulas, $results, $questions);
    }

    protected function getFormulasFromText($text)
    {
        preg_match_all('/data-tex\s*=\s*"([^\"]*)"/', $text, $matches);
        $formulas = $matches[1] ?? [];

        return $formulas;
    }

    protected function replaceQuestions($formulas, $results, $questions)
    {
        $replaceFormulas = array_combine($formulas, $results);
        $questions = array_map(function ($question) use ($replaceFormulas) {
            $question['stem'] = $this->getReplaceTexts($replaceFormulas, $question['stem']);
            if (isset($question['options'])) {
                $question['options'] = array_map(function ($option) use ($replaceFormulas) {
                    return $this->getReplaceTexts($replaceFormulas, $option);
                }, $question['options']);
            }

            return $question;
        }, $questions);

        return $questions;
    }

    protected function getReplaceTexts($replaceFormulas, $text)
    {
        $replaceFunc = function ($match) use ($replaceFormulas) {
            return "<img src=\"{$replaceFormulas[$match[1]]}\" >";
        };

        return preg_replace_callback('/<span.*?data-tex\s*=\s*"(.*?)".*?><\/span>/', $replaceFunc, $text);
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
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
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
