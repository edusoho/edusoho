<?php

namespace AppBundle\Controller\Question;

use AppBundle\Common\FileToolkit;
use AppBundle\Common\TimeMachine;
use AppBundle\Controller\BaseController;
use Biz\Question\QuestionParseClient;
use Biz\Question\Traits\QuestionImportTrait;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\User\Service\TokenService;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
use Symfony\Component\HttpFoundation\File\File as FileObject;
use Symfony\Component\HttpFoundation\Request;

class QuestionParserController extends BaseController
{
    use QuestionImportTrait;

    public function readAction(Request $request, $type, $questionBank)
    {
        $templateInfo = $this->getTemplateInfo($type);
        if (!$request->isMethod('POST')) {
            return $this->render($templateInfo['readModalTemplate'], [
                'questionBank' => $questionBank,
            ]);
        }

        $file = $request->files->get('importFile');

        if (!$this->isFileExtensionValid($file)) {
            return $this->render($templateInfo['readErrorModalTemplate'], [
                'questionBank' => $questionBank,
            ]);
        }

        try {
            $token = $this->parseQuestionThenMakeToken($questionBank['id'], $file);
        } catch (\Exception $e) {
            return $this->render($templateInfo['readErrorModalTemplate'], [
                'questionBank' => $questionBank,
            ]);
        }

        return $this->createJsonResponse([
            'url' => $this->generateUrl($templateInfo['reEditRoute'], [
                'token' => $token,
                'categoryId' => $request->request->get('category_Id'),
            ]),
            'success' => true,
            'progressUrl' => $this->generateUrl('question_parse_progress', ['token' => $token]),
        ]);
    }

    public function reEditAction(Request $request, $token, $type)
    {
        $token = $this->getTokenService()->verifyToken('upload.course_private_file', $token);
        if (empty($token)) {
            throw new \Exception('超过有效期');
        }
        $data = $token['data'];
        if (!$this->getQuestionBankService()->canManageBank($data['questionBankId'])) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($data['questionBankId']);
        $categoryTree = $this->getItemCategoryService()->getItemCategoryTree($questionBank['itemBankId']);
        $itemsJson = file_get_contents($data['cacheFilePath']);
        $categoryId = $request->query->get('categoryId', 0);
        $category = $this->getItemCategoryService()->getItemCategory($categoryId);
        $itemsJson = $this->addEmphasisStyle($itemsJson);
        $items = json_decode($itemsJson, true);
        foreach ($items as &$item) {
            $item['category_id'] = $categoryId;
            $item['category_name'] = $category['name'];
        }
        $templateInfo = $this->getTemplateInfo($type);

        return $this->render($templateInfo['reEditTemplate'], [
            'filename' => mb_substr(str_replace('.docx', '', $data['filename']), 0, 50, 'utf-8'),
            'items' => $items,
            'questionBankId' => $questionBank['id'],
            'itemBankId' => $questionBank['itemBankId'],
            'categoryTree' => $categoryTree,
            'type' => $type,
            'categoryId' => $categoryId,
        ]);
    }

    protected function isFileExtensionValid($file)
    {
        $extension = FileToolkit::getFileExtension($file);

        return in_array($extension, ['docx', 'xlsx']);
    }

    protected function parseQuestionThenMakeToken($questionBankId, $file)
    {
        $uploadFile = $this->getFileService()->uploadFile('tmp', $file);
        $client = new QuestionParseClient();
        $jobId = $client->parse($uploadFile['file']->getRealPath());

        $token = $this->getTokenService()->makeToken('upload.course_private_file', [
            'data' => [
                'filename' => $file->getClientOriginalName(),
                'questionBankId' => $questionBankId,
                'jobId' => $jobId,
                'cacheFilePath' => $uploadFile['file']->getRealPath().'json',
            ],
            'duration' => TimeMachine::ONE_DAY,
            'userId' => $this->getCurrentUser()->getId(),
        ]);
        $this->getFileService()->deleteFile($uploadFile['id']);

        return $token['token'];
    }

    protected function parseQuestions($fullpath)
    {
        $tmpPath = $this->get('kernel')->getContainer()->getParameter('topxia.upload.public_directory').'/tmp';
        $text = $this->getItemService()->readWordFile($fullpath, $tmpPath);
        $self = $this;
        $fileService = $this->getFileService();
        $text = preg_replace_callback(
            '/<img src=[\'\"](.*?)[\'\"]/',
            function ($matches) use ($self, $fileService) {
                $file = new FileObject($matches[1]);
                $result = $fileService->uploadFile('course', $file);
                $url = $self->get('web.twig.extension')->getFpath($result['uri']);

                return "<img src=\"{$url}\"";
            },
            $text
        );

        return $this->getItemService()->parseItems($text);
    }

    protected function getTemplateInfo($type)
    {
        if (!in_array($type, ['testpaper', 'item'])) {
            return $this->createNotFoundException('parser type not found');
        }

        $info = [];

        if ('testpaper' == $type) {
            $info = [
                'readModalTemplate' => 'testpaper/manage/read-modal.html.twig',
                'readErrorModalTemplate' => 'testpaper/manage/read-error.html.twig',
                'reEditRoute' => 'testpaper_re_edit',
                'reEditTemplate' => 'question-manage/re-edit.html.twig',
            ];
        }

        if ('item' == $type) {
            $info = [
                'readModalTemplate' => 'question-manage/read-modal.html.twig',
                'readErrorModalTemplate' => 'question-manage/read-error.html.twig',
                'reEditRoute' => 'question_re_edit',
                'reEditTemplate' => 'question-manage/re-edit.html.twig',
            ];
        }

        return $info;
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

    /**
     * @return ItemCategoryService
     */
    protected function getItemCategoryService()
    {
        return $this->createService('ItemBank:Item:ItemCategoryService');
    }
}
