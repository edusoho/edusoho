<?php

namespace AppBundle\Controller;

use Biz\File\Service\UploadFileService;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CloudController extends BaseController
{
    public function testTikuAction()
    {
        return $this->render('login/test.html.twig', array(
        ));
    }

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

    public function videoFingerprintAction(Request $request)
    {
        return new Response($this->get('web.twig.extension')->getFingerprint());
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
        $values = array_filter($values, function ($value) {
            return !is_array($value);
        });

        return $this->get('web.twig.extension')->simpleTemplateFilter($pattern, $values);
    }

    protected function checkSign($server, $sign, $secretKey)
    {
        return md5($server.$secretKey) == $sign;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }
}
