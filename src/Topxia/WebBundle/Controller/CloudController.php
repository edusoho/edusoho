<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CloudController extends BaseController
{
    public function setServerAction(Request $request)
    {
        $server = $request->query->get('server');
        $sign = $request->query->get('sign');

        if (empty($server)) {
            return $this->createJsonResponse(array('error' => 'server param is empty.'));
        }

        if (empty($sign)) {
            return $this->createJsonResponse(array('error' => 'sign param is empty.'));
        }

        $setting = $this->getSettingService()->get('storage', array());

        if (empty($setting['cloud_secret_key'])) {
            return $this->createJsonResponse(array('error' => 'secret key not set.'));
        }

        if (!$this->checkSign($server, $sign, $setting['cloud_secret_key'])) {
            return $this->createJsonResponse(array('error' => 'sign error.'));
        }

        $setting['cloud_api_server'] = $server;

        $this->getSettingService()->set('storage', $setting);

        return $this->createJsonResponse(true);
    }

    public function reconvertOldFileAction(Request $request)
    {
        $sign = $request->query->get('sign');
        if (empty($sign)) {
            return $this->createJsonResponse(array('error' => 'sign param is empty.'));
        }

        $setting = $this->getSettingService()->get('storage', array());
        if (empty($setting['cloud_secret_key'])) {
            return $this->createJsonResponse(array('error' => 'secret key not set.'));
        }

        $fileId = $request->query->get('id');
        $pipeline = $request->query->get('pipeline');

        if ($sign != md5($fileId . $setting['cloud_secret_key'])) {
            return $this->createJsonResponse(array('error' => 'sign error.'));
        }

        $callback = $this->generateUrl('uploadfile_cloud_convert_callback3', array(), true);

        $result = $this->getUploadFileService()->reconvertOldFile($fileId, $callback, $pipeline);
        return $this->createJsonResponse($result);
    }

    public function oldkeysAction(Request $request)
    {
        $sign = $request->query->get('sign');
        if (empty($sign)) {
            return $this->createJsonResponse(array('error' => 'sign param is empty.'));
        }

        $setting = $this->getSettingService()->get('storage', array());
        if (empty($setting['cloud_secret_key'])) {
            return $this->createJsonResponse(array('error' => 'secret key not set.'));
        }

        if ($sign != md5($setting['cloud_secret_key'])) {
            return $this->createJsonResponse(array('error' => 'sign error.'));
        }

        $conditions = array(
            'storage' => 'cloud',
        );
        $count = $this->getUploadFileService()->searchFileCount($conditions);

        $files = $this->getUploadFileService()->searchFiles($conditions, 'latestCreated', 0, $count);

        foreach ($files as &$file) {
            $file['metas'] = empty($file['metas']) ?  array() : json_decode($file['metas'], true);
            $file['metas2'] = empty($file['metas2']) ?  array() : json_decode($file['metas2'], true);
            $file['convertParams'] = empty($file['convertParams']) ?  array() : json_decode($file['convertParams'], true);
        }

        return $this->createJsonResponse($files);
    }

    public function videoFingerprintAction(Request $request)
    {
        $userId = $request->query->get('userId');
        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            return new Response('');
        }

        $host = $request->getHttpHost();

        return new Response("{$host} {$user['nickname']}");
    }

    private function checkSign($server, $sign, $secretKey)
    {
        return md5($server . $secretKey) == $sign;
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    private function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

}