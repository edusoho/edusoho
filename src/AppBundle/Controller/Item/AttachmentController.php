<?php

namespace AppBundle\Controller\Item;

use AppBundle\Controller\BaseController;
use Biz\CloudFile\Service\CloudFileService;
use Biz\File\Service\UploadFileService;
use Biz\MaterialLib\Service\MaterialLibService;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\System\Service\SettingService;
use Codeages\Biz\ItemBank\Item\Service\AttachmentService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AttachmentController extends BaseController
{
    public function initUploadAction(Request $request)
    {
        $token = $request->query->get('uploaderToken');
        $params = $this->parseToken($token);

        if (!$params) {
            return $this->createJsonResponse(['error' => '上传授权码不正确，请重试！']);
        }

        $params = array_merge($request->query->all(), $params);

        $result = $this->getUploadFileService()->initUploadAttachment($params);
        $result['uploadProxyUrl'] = $this->generateUrl('uploader_entry', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $result['authUrl'] = $this->generateUrl('uploader_auth', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->createJsonpResponse($result, $request->query->get('callback'));
    }

    public function finishUploadAction(Request $request)
    {
        $token = $request->query->get('uploaderToken');
        $params = $this->parseToken($token);

        if (!$params) {
            return $this->createJsonResponse(['error' => '上传授权码不正确，请重试！']);
        }

        $params = array_merge($request->query->all(), $params);

        try {
            $file = $this->getUploadFileService()->finishUploadAttachment($params);
        } catch (\Exception $e) {
            return $this->createJsonpResponse(['error' => $e->getMessage()], $request->query->get('callback'), method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500);
        }

        return $this->createJsonpResponse($file, $request->query->get('callback'));
    }

    public function deleteAction(Request $request)
    {
        $id = $request->request->get('id', 0);
        if (!$this->canDeleteAttachment($request)) {
            $this->createJsonResponse(['result' => false, 'msg' => '您无权删除该附件']);
        }

        $result = $this->getUploadFileService()->deleteAttachment($id);
        if ($result) {
            return $this->createJsonResponse(['result' => true, 'msg' => '附件删除成功']);
        }

        return $this->createJsonResponse(['result' => false, 'msg' => '附件删除失败']);
    }

    public function previewAction(Request $request)
    {
        $id = $request->request->get('id', 0);
        $user = $this->getUser();

        if (!$user->isLogin()) {
            return $this->createJsonResponse(['result' => false, 'msg' => '用户未登录', 'data' => []]);
        }

        $attachment = $this->getAttachmentService()->getAttachment($id);
        if (empty($attachment)) {
            return $this->createJsonResponse(['result' => false, 'msg' => '文件不存在', 'data' => []]);
        }

        $file = $this->getCloudFileService()->getByGlobalId($attachment['global_id']);
        if (empty($file)) {
            return $this->createJsonResponse(['result' => false, 'msg' => '文件不存在', 'data' => []]);
        }

        $ssl = $request->isSecure() ? true : false;
        if (in_array($file['type'], ['video', 'ppt', 'document'])) {
            return $this->globalPlayer($file, $ssl);
        } elseif ('audio' == $file['type']) {
            return $this->audioPlayer($file, $ssl);
        }

        return $this->createJsonResponse(['result' => false, 'msg' => '无法预览该类文件', 'data' => []]);
    }

    protected function globalPlayer($file, $ssl)
    {
        $user = $this->getCurrentUser();
        $player = $this->getMaterialLibService()->player($file['globalId'], $ssl);

        return $this->createJsonResponse(['result' => true, 'msg' => '', 'data' => [
            'token' => $player['token'],
            'resNo' => $file['globalId'],
            'user' => ['id' => $user['id'], 'name' => $user['nickname']],
            'type' => $file['type'],
        ]]);
    }

    protected function audioPlayer($file, $ssl)
    {
        $user = $this->getCurrentUser();
        $player = $this->getMaterialLibService()->player($file['no'], $ssl);
        $setting = $this->getSettingService()->get('storage', []);

        return $this->createJsonResponse(['result' => true, 'msg' => '', 'data' => [
            'playlist' => $player['url'],
            'type' => $file['type'],
            'statsInfo' => [
                'accesskey' => empty($setting['cloud_access_key']) ? '' : $setting['cloud_access_key'],
                'globalId' => $file['globalId'],
                'userId' => $user['id'],
                'userName' => $user['nickname'],
            ],
        ]]);
    }

    public function downloadAction(Request $request)
    {
        $id = $request->request->get('id', 0);
        $user = $this->getUser();
        if (!$user->isLogin()) {
            return $this->createJsonResponse(['result' => false, 'msg' => '用户未登录', 'url' => '']);
        }

        $ssl = $request->isSecure() ? true : false;
        $result = $this->getUploadFileService()->downloadAttachment($id, $ssl);
        if (!empty($result['url'])) {
            return $this->createJsonResponse(['result' => true, 'msg' => '', 'url' => $result['url']]);
        }

        return $this->createJsonResponse(['result' => false, 'msg' => '获取文件失败', 'url' => '']);
    }

    protected function canDeleteAttachment($id)
    {
        $user = $this->getCurrentUser();
        if ($user->isAdmin()) {
            return true;
        }
        $attachment = $this->getAttachmentService()->getAttachment($id);
        if (!empty($attachment) && $attachment['created_user_id'] == $user['id']) {
            return true;
        }

        return false;
    }

    protected function parseToken($token)
    {
        $setting = $this->getSettingService()->get('storage', []);
        $accessKey = empty($setting['cloud_access_key']) ? '' : $setting['cloud_access_key'];
        $secretKey = empty($setting['cloud_secret_key']) ? '' : $setting['cloud_secret_key'];

        return $this->getAttachmentService()->parseToken($token, $accessKey, $secretKey);
    }

    /**
     * @return AttachmentService
     */
    protected function getAttachmentService()
    {
        return $this->getBiz()->service('ItemBank:Item:AttachmentService');
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
     * @return CloudFileService
     */
    protected function getCloudFileService()
    {
        return $this->createService('CloudFile:CloudFileService');
    }

    /**
     * @return MaterialLibService
     */
    protected function getMaterialLibService()
    {
        return $this->createService('MaterialLib:MaterialLibService');
    }
}
