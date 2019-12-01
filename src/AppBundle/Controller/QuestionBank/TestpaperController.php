<?php

namespace AppBundle\Controller\QuestionBank;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Biz\Question\Service\QuestionService;
use Biz\QuestionBank\Service\QuestionBankService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\Testpaper\TestpaperException;
use ExamParser\Writer\WriteDocx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TestpaperController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->validateCanManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);

        $conditions = array(
            'bankId' => $questionBank['id'],
            'type' => 'testpaper',
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getTestpaperService()->searchTestpaperCount($conditions),
            10
        );

        $testpapers = $this->getTestpaperService()->searchTestpapers(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($testpapers, 'updatedUserId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $testpaperActivities = $this->getTestpaperActivityService()->findActivitiesByMediaIds(ArrayToolkit::column($testpapers, 'id'));

        return $this->render('question-bank/testpaper/index.html.twig', array(
            'questionBank' => $questionBank,
            'testpapers' => $testpapers,
            'users' => $users,
            'paginator' => $paginator,
            'testpaperActivities' => $testpaperActivities,
        ));
    }

    public function getTestpaperHtmlAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->validateCanManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);

        $conditions = array(
            'bankId' => $questionBank['id'],
            'type' => 'testpaper',
            'keyword' => $request->query->get('keyword', ''),
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getTestpaperService()->searchTestpaperCount($conditions),
            10
        );

        $testpapers = $this->getTestpaperService()->searchTestpapers(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($testpapers, 'updatedUserId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $testpaperActivities = $this->getTestpaperActivityService()->findActivitiesByMediaIds(ArrayToolkit::column($testpapers, 'id'));

        return $this->render('question-bank/testpaper/testpaper-list-tr.html.twig', array(
            'questionBank' => $questionBank,
            'testpapers' => $testpapers,
            'users' => $users,
            'paginator' => $paginator,
            'testpaperActivities' => $testpaperActivities,
        ));
    }

    public function importAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->validateCanManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);

        return $this->forward('AppBundle:Question/QuestionParser:read', array(
            'request' => $request,
            'type' => 'testpaper',
            'questionBank' => $questionBank,
        ));
    }

    public function createAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->validateCanManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);

        $types = $this->getQuestionTypes();

        return $this->render('question-bank/testpaper/manage/testpaper-form.html.twig', array(
            'types' => $types,
            'questionBank' => $questionBank,
        ));
    }

    public function editAction(Request $request, $id, $testpaperId)
    {
        if (!$this->getQuestionBankService()->validateCanManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);

        if (!$testpaper || $testpaper['bankId'] != $id) {
            return $this->createMessageResponse('error', 'testpaper not found');
        }

        if ('draft' != $testpaper['status']) {
            return $this->createMessageResponse('error', '已发布或已关闭的试卷不能再修改题目');
        }

        $questions = $this->getTestpaperService()->showTestpaperItems($testpaper['id']);
        $questionCategories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($questions, 'categoryId'));

        return $this->render('question-bank/testpaper/manage/testpaper-form.html.twig', array(
            'questionBank' => $questionBank,
            'testpaper' => $testpaper,
            'questions' => $questions,
            'subCounts' => empty($questions['material']) ? 0 : array_sum(array_column($questions['material'], 'subCount')),
            'questionCategories' => $questionCategories,
        ));
    }

    public function deleteTestpapersAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->validateCanManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $ids = $request->request->get('ids');

        $testpapers = $this->getTestpaperService()->findTestpapersByIds($ids);
        if (empty($testpapers)) {
            $this->createNewException(TestpaperException::NOTFOUND_TESTPAPER());
        }

        $this->getTestpaperService()->deleteTestpapers($ids);

        return $this->createJsonResponse(true);
    }

    public function deleteAction(Request $request, $id, $testpaperId)
    {
        if (!$this->getQuestionBankService()->validateCanManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);

        if (empty($testpaper) || $testpaper['bankId'] != $id) {
            return $this->createMessageResponse('error', 'testpaper not found');
        }

        $this->getTestpaperService()->deleteTestpaper($testpaperId);

        return $this->createJsonResponse(true);
    }

    public function publishAction(Request $request, $id, $testpaperId)
    {
        if (!$this->getQuestionBankService()->validateCanManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);
        if (empty($testpaper) || $testpaper['bankId'] != $id) {
            $this->createNewException(TestpaperException::NOTFOUND_TESTPAPER());
        }

        $testpaper = $this->getTestpaperService()->publishTestpaper($testpaperId);

        $user = $this->getUserService()->getUser($testpaper['updatedUserId']);

        return $this->createJsonResponse(true);
    }

    public function closeAction(Request $request, $id, $testpaperId)
    {
        if (!$this->getQuestionBankService()->validateCanManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);
        if (empty($testpaper) || $testpaper['bankId'] != $id) {
            $this->createNewException(TestpaperException::NOTFOUND_TESTPAPER());
        }

        $testpaper = $this->getTestpaperService()->closeTestpaper($testpaperId);

        $user = $this->getUserService()->getUser($testpaper['updatedUserId']);

        return $this->createJsonResponse(true);
    }

    public function previewAction(Request $request, $id, $testpaperId)
    {
        if (!$this->getQuestionBankService()->validateCanManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);
        if (!$testpaper || $testpaper['bankId'] != $id) {
            return $this->createMessageResponse('error', 'testpaper not found');
        }

        if ('closed' === $testpaper['status']) {
            return $this->createMessageResponse('warning', 'testpaper already closed');
        }

        $questions = $this->getTestpaperService()->showTestpaperItems($testpaper['id']);

        $total = $this->getTestpaperService()->countQuestionTypes($testpaper, $questions);

        $attachments = $this->getTestpaperService()->findAttachments($testpaper['id']);

        return $this->render('testpaper/manage/preview.html.twig', array(
            'questions' => $questions,
            'limitedTime' => $testpaper['limitedTime'],
            'paper' => $testpaper,
            'paperResult' => array(),
            'total' => $total,
            'attachments' => $attachments,
            'questionTypes' => $this->getTestpaperService()->getCheckedQuestionTypeBySeq($testpaper),
        ));
    }

    public function exportAction(Request $request, $id, $testpaperId)
    {
        if (!$this->getQuestionBankService()->validateCanManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);

        if (empty($testpaper) || $testpaper['bankId'] != $id) {
            return $this->createMessageResponse('error', 'testpaper not found');
        }

        $questions = $this->getTestpaperService()->buildExportTestpaperItems($testpaperId);

        $fileName = $testpaper['name'].'.docx';
        $baseDir = $this->get('kernel')->getContainer()->getParameter('topxia.disk.local_directory');
        $path = $baseDir.DIRECTORY_SEPARATOR.$fileName;

        $writer = new WriteDocx($path);
        $writer->write($questions);

        $headers = array(
            'Content-Type' => 'application/msword',
            'Content-Disposition' => 'attachment; filename='.$fileName,
        );

        return new BinaryFileResponse($path, 200, $headers);
    }

    public function jsonAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->validateCanManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $conditions = array(
            'bankId' => $id,
            'type' => 'testpaper',
            'keyword' => $request->query->get('keyword', ''),
        );
        $totalCount = $this->getTestpaperService()->searchTestpaperCount($conditions);
        $conditions['status'] = 'open';
        $openCount = $this->getTestpaperService()->searchTestpaperCount($conditions);

        $pagination = new Paginator(
            $request,
            $openCount,
            10
        );

        $testpapers = $this->getTestpaperService()->searchTestpapers(
            $conditions,
            array('createdTime' => 'DESC'),
            $pagination->getOffsetCount(),
            $pagination->getPerPageCount()
        );

        foreach ($testpapers as &$testpaper) {
            $testpaper = ArrayToolkit::parts($testpaper, array(
                'id',
                'name',
            ));
        }

        $data = array(
            'testpapers' => $testpapers,
            'totalCount' => $totalCount,
            'openCount' => $openCount,
        );

        return $this->createJsonResponse($data);
    }

    public function questionPickAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->validateCanManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('question-bank/common/question-pick-modal.html.twig', array(
        ));
    }

    protected function getQuestionTypes()
    {
        $typesConfig = $this->get('extension.manager')->getQuestionTypes();

        $types = array();
        foreach ($typesConfig as $type => $typeConfig) {
            $types[$type] = array(
                'name' => $typeConfig['name'],
                'hasMissScore' => $typeConfig['hasMissScore'],
                'seqNum' => $typeConfig['seqNum'],
            );
        }

        return $types;
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->createService('Activity:TestpaperActivityService');
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    protected function getCategoryService()
    {
        return $this->createService('Question:CategoryService');
    }
}
