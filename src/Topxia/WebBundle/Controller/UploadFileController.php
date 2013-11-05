<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Topxia\Service\Util\CloudClientFactory;
use Topxia\Common\StringToolkit;
use Topxia\Common\FileToolkit;

class UploadFileController extends BaseController
{

    public function uploadAction(Request $request)
    {
        $token = $request->request->get('token');
        $token = $this->getUserService()->getToken('fileupload', $token);
        if (empty($token)) {
            throw $this->createAccessDeniedException('上传TOKEN已过期或不存在。');
        }

        $targetType = $request->query->get('targetType');
        $targetId = $request->query->get('targetId');

        $originalFile = $this->get('request')->files->get('file');

        $file = $this->getUploadFileService()->addFile($targetType, $targetId, array(), 'local', $originalFile);
        return $this->createJsonResponse($file);
    }

    // @todo 权限验证
    public function browserAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $conditions = $request->query->all();

        $files = $this->getUploadFileService()->searchFiles($conditions, 'latestUpdated', 0, 1000);
        
        return $this->createFilesJsonResponse($files);
    }

    public function paramsAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $targetType = $request->query->get('targetType');
        $targetId = $request->query->get('targetId');

        $params = array();

        $setting = $this->setting('storage');
        if ($setting['upload_mode'] == 'cloud') {
            $params['mode'] = 'cloud';

            $factory = new CloudClientFactory();
            $client = $factory->createClient();

            $convertor = $request->query->get('convertor');
            $commands = null;
            if ($convertor == 'video') {
                $commands = array_keys($client->getVideoConvertCommands());
            } elseif ($convertor == 'audio') {
            }

            $clientParams = array();
            if ($commands) {
                $clientParams = array(
                    'convertCommands' => implode(';', $commands),
                    'convertNotifyUrl' => $this->generateUrl('uploadfile_convert_callback', array('key' => $convertKey), true),
                );
            }

            $uploadToken = $client->generateUploadToken($client->getBucket(), $clientParams);
            if (!empty($uploadToken['error'])) {
                throw \RuntimeException('创建上传TOKEN失败！');
            }

            $params['url'] = $uploadToken['url'];

            //@todo refacor it.
            $keySuffix = substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 16);
            $key = "{$targetType}-{$targetId}/{$keySuffix}";

            $params['postParams'] = array(
                'token' => $uploadToken['token'],
                'key' => $key,
            );

        } else {
            $params['mode'] = 'local';
            $params['url'] = $this->generateUrl('uploadfile_upload', array('targetType' => $targetType, 'targetId' => $targetId));
            $params['postParams'] = array(
                'token' => $this->getUserService()->makeToken('fileupload', $user['id'], strtotime('+ 2 hours')),
            );
        }

        return $this->createJsonResponse($params);
    }

    public function cloudCallbackAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $targetType = $request->query->get('targetType');
        $targetId = $request->query->get('targetId');
        $fileInfo = $request->request->all();

        $file = $this->getUploadFileService()->addFile($targetType, $targetId, $fileInfo, 'cloud');
        return $this->createJsonResponse($file);
    }

    public function cloudFileinfoAction(Request $request)
    {
        $type = $request->query->get('type', '');
        $key = $request->query->get('key', '');

        if (empty($key)) {
            return $this->createNotFoundException();
        }

        $factory = new CloudClientFactory();
        $client = $factory->createClient();

        if ($type == 'video') {
            $info = $client->getVideoInfo($client->getBucket(), $key);
        } else if ($type == 'audio') {
            $info = $client->getAudioInfo($client->getBucket(), $key);
        } else {
            $info = array();
        }

        if (!empty($info['duration'])) {
            $info['duration'] = StringToolkit::secondsToText($info['duration']);
        }

        return $this->createJsonResponse($info);
    }

    private function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    private function createFilesJsonResponse($files)
    {
        foreach ($files as &$file) {
            $file['updatedTime'] = date('Y-m-d H:i', $file['updatedTime']);
            $file['size'] = FileToolkit::formatFileSize($file['size']);
            unset($file);
        }
        return $this->createJsonResponse($files);
    }

}