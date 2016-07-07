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

        $files = $this->getUploadFileService()->searchFiles($conditions, array('createdTime','DESC'), 0, $count);

        foreach ($files as &$file) {
            $file['metas'] = empty($file['metas']) ?  array() : json_decode($file['metas'], true);
            $file['metas2'] = empty($file['metas2']) ?  array() : json_decode($file['metas2'], true);
            $file['convertParams'] = empty($file['convertParams']) ?  array() : json_decode($file['convertParams'], true);
        }

        return $this->createJsonResponse($files);
    }

    public function videoFingerprintAction(Request $request)
    {
        return new Response($this->get('topxia.twig.web_extension')->getFingerprint());
    }

    public function docWatermarkAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return new Response('');
        }

        $pattern = $this->setting('magic.doc_watermark');
        if ($pattern) {
            $watermark = $this->parsePattern($pattern, $user->toArray());
        } else {
            $watermark = '';
        }

        return new Response($watermark);
    }

    public function pptWatermarkAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return new Response('');
        }

        $pattern = $this->setting('magic.ppt_watermark');
        if ($pattern) {
            $watermark = $this->parsePattern($pattern, $user->toArray());
        } else {
            $watermark = '';
        }

        return new Response($watermark);
    }

    public function testpaperWatermarkAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return new Response('');
        }

        $pattern = $this->setting('magic.testpaper_watermark');
        if ($pattern) {
            $watermark = $this->parsePattern($pattern, $user->toArray());
        } else {
            $watermark = '';
        }
  
        return $this->createJsonResponse($watermark);
    }

    protected function parsePattern($pattern, $user)
    {
        $profile = $this->getUserService()->getUserProfile($user['id']);

        $values = array_merge($user, $profile);
        $values = array_filter($values, function($value){
            return !is_array($value);
        });
        return $this->get('topxia.twig.web_extension')->simpleTemplateFilter($pattern, $values);
    }

    protected function checkSign($server, $sign, $secretKey)
    {
        return md5($server . $secretKey) == $sign;
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

}