<?php

namespace AppBundle\Controller\Question;

use AppBundle\Common\FileToolkit;
use AppBundle\Controller\BaseController;
use Biz\Content\Service\FileService;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\User\Service\TokenService;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File as FileObject;
use Symfony\Component\HttpFoundation\Request;

class QuestionParserController extends BaseController
{
    public function readAction(Request $request, $type, $questionBank)
    {
        $templateInfo = $this->getTemplateInfo($type);
        if ($request->isMethod('POST')) {
            $file = $request->files->get('importFile');

            if ('docx' == FileToolkit::getFileExtension($file)) {
                $result = $this->getFileService()->uploadFile('course_private', $file);
                $uploadFile = $this->getFileService()->parseFileUri($result['uri']);
                try {
                    $items = $this->parseQuestions($uploadFile['fullpath']);
                } catch (\Exception $e) {
                    return $this->render($templateInfo['readErrorModalTemplate']);
                }

                $token = $this->getTokenService()->makeToken('upload.course_private_file', [
                    'data' => [
                        'id' => $result['id'],
                        'filename' => $file->getClientOriginalName(),
                        'fileuri' => $result['uri'],
                        'filepath' => $uploadFile['fullpath'],
                        'questionBankId' => $questionBank['id'],
                        'cacheFilePath' => $this->cacheQuestions($items, $uploadFile),
                    ],
                    'duration' => 86400,
                    'userId' => $this->getCurrentUser()->getId(),
                ]);

                return $this->createJsonResponse([
                    'url' => $this->generateUrl($templateInfo['reEditRoute'], [
                        'token' => $token['token'],
                    ]),
                    'success' => true,
                ]);
            } else {
                return $this->render($templateInfo['readErrorModalTemplate']);
            }
        }

        return $this->render($templateInfo['readModalTemplate'], [
            'questionBank' => $questionBank,
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
        $items = json_decode($itemsJson, true);

        $templateInfo = $this->getTemplateInfo($type);

        return $this->render($templateInfo['reEditTemplate'], [
            'filename' => mb_substr(str_replace('.docx', '', $data['filename']), 0, 50, 'utf-8'),
            'items' => $items,
            'questionBankId' => $questionBank['itemBankId'],
            'categoryTree' => $categoryTree,
            'type' => $type,
        ]);
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

    protected function cacheQuestions($questions, $uploadFile)
    {
        $fileSystem = new Filesystem();
        $filePath = $uploadFile['fullpath'].'json';
        $fileSystem->dumpFile($filePath, json_encode($questions));

        return $filePath;
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

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }
}
