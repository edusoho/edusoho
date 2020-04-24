<?php

namespace AppBundle\Controller\Item;

use AppBundle\Common\FileToolkit;
use AppBundle\Controller\BaseController;
use Biz\File\Service\Impl\CloudFileImplementorImpl;
use Biz\File\Service\UploadFileService;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\System\Service\SettingService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Item\Service\ItemAttachmentService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AttachmentController extends BaseController
{
    public function initUploadAction(Request $request)
    {
        $token = $request->query->get('token');
        $params = $this->parseToken($token);

        if (!$params) {
            return $this->createJsonResponse(array('error' => '上传授权码不正确，请重试！'));
        }

        $params = array_merge($request->query->all(), $params);

        $result = $this->getUploadFileService()->initUploadAttachment($params);
        $result['uploadProxyUrl'] = $this->generateUrl('uploader_entry', array(), UrlGeneratorInterface::ABSOLUTE_URL);
        $result['authUrl'] = $this->generateUrl('uploader_auth', array(), UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->createJsonpResponse($result, $request->query->get('callback'));
    }

    public function finishUploadAction(Request $request)
    {
        $token = $request->query->get('token');
        $params = $this->parseToken($token);

        if (!$params) {
            return $this->createJsonResponse(array('error' => '上传授权码不正确，请重试！'));
        }

        $params = array_merge($request->query->all(), $params);

        try {
            $file = $this->getUploadFileService()->finishUploadAttachment($params);
        } catch (\Exception $e) {
            return $this->createJsonpResponse(array('error' => $e->getMessage()), $request->query->get('callback'), method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500);
        }

        return $this->createJsonpResponse($file, $request->query->get('callback'));
    }

    public function deleteAction(Request $request, $id)
    {
        if (!$this->canDeleteAttachment($request)) {
            $this->createJsonResponse(array('result' => false,'msg' => '您无权删除该附件'));
        }

        $result = $this->getUploadFileService()->deleteAttachment($id);
        if ($result) {
            return $this->createJsonResponse(array('result' => true,'msg' => '附件删除成功'));
        }

        return $this->createJsonResponse(array('result' => false,'msg' => '附件删除失败'));
    }

    protected function canDeleteAttachment($request)
    {
        $type = $request->query->get('targetType', '');
        $id = $request->query->get('targetId', 0);
        $user = $this->getCurrentUser();
        if ($user->isAdmin()) {
            return true;
        }

        if ('item' == $type) {
            return $this->getQuestionBankService()->canManageBank($id);
        }

        if ('answer' == $type) {
            $answerRecord = $this->getAnswerRecordService()->get($id);
            if (!empty($answerRecord) && $answerRecord['user_id'] == $user['id']) {
                return true;
            }
        }

        return false;
    }

    protected function parseToken($token)
    {
        $setting = $this->getSettingService()->get('storage', array());
        $accessKey = empty($setting['cloud_access_key']) ? '' : $setting['cloud_access_key'];
        $secretKey = empty($setting['cloud_secret_key']) ? '' : $setting['cloud_secret_key'];

        return $this->getItemAttachmentService()->parseToken($token, $accessKey, $secretKey);
    }

    /**
     * @return ItemAttachmentService
     */
    protected function getItemAttachmentService()
    {
        return $this->getBiz()->service('ItemBank:Item:ItemAttachmentService');
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->getBiz()->service('QuestionBank:QuestionBankService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->createService('ItemBank:Answer:AnswerRecordService');
    }
}