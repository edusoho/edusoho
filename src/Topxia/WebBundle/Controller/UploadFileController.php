<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Topxia\Service\Util\CloudClientFactory;
use Topxia\Common\StringToolkit;

class UploadFileController extends BaseController
{

    public function uploadAction(Request $request)
    {

    }

    public function browserAction(Request $request)
    {
        
    }

    public function paramsAction(Request $request)
    {

        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

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
            $params['postParams'] = array(
                'token' => $uploadToken['token'],
            );

        } else {
            $params['mode'] = 'local';
            $params['url'] = $this->generateUrl('uploadfile_upload');
            $parmas['postParams'] = array(
                'token' => $this->getUserService()->makeToken('fileupload', $user['id'], strtotime('+ 2 hours')),
            );
        }

        return $this->createJsonResponse($params);
    }

}