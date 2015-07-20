<?php

namespace Topxia\Service\File\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\File\UploadFileService2;
use Topxia\Service\CloudPlatform\Client\CloudAPI;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Topxia\Service\Common\ServiceKernel;
    
class UploadFileService2Impl extends BaseService implements UploadFileService2
{
	static $implementor = array(
        'local'=>'File.LocalFileImplementor2',
        'cloud' => 'File.CloudFileImplementor2',
    );

    public function initUpload($params)
    {
    	$user = $this->getCurrentUser();
        if (empty($user)) {
            throw $this->createServiceException("用户未登录，上传初始化失败！");
        }

        if (!ArrayToolkit::requireds($params, array('targetId', 'targetType'))) {
            throw $this->createServiceException("参数缺失，上传初始化失败！");
        }

        $setting = $this->getSettingService()->get('storage');
        $params['storage'] = empty($setting['upload_mode']) ? 'local' : $setting['upload_mode'];

        $implementor = $this->getFileImplementorByStorage($params['storage']);

        $file = $this->getUploadFileDao()->addFile($implementor->prepareUpload($params));

        $file['bucket'] = $params['bucket'];

        $params = $implementor->initUpload($file);

        $file = $this->getUploadFileDao()->updateFile($file['id'], array('globalId' => $params['globalId']));

        return $params;
    }

    public function finishedUpload($fileId)
    {
    	$file = $this->getUploadFileDao()->updateFile($fileId, array('status' => 'ok'));

        // $api = new CloudAPI(array(
        //     'accessKey' => '111',
        //     'secretKey' => '222',
        //     'apiUrl' => 'http://api.pcloud.com',
        //     'debug' => true,
        // ));

        // $logger = new Logger('CloudAPI');
        // $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir') . '/cloud-api.log', Logger::DEBUG));
        // $api->setLogger($logger);



        // $task = array();
        // $task['bucket'] = 'private';
        // $task['key'] = '1.mp4';
        // $task['processor'] = 'video';
        // $task['directives'] = array(
        //     'type' => 'cmccVideo',
        // );

        // $task['callbackUrl'] = '';

        // $result = $api->post('/processes', $task);

        // var_dump($result);



    }

    protected function getFileImplementor($file)
    {
    	return $this->getFileImplementorByStorage($file['storage']);
    }

    protected function getFileImplementorByStorage($storage)
    {
        return $this->createFileImplementor($storage);
    }

    protected function createFileImplementor($key)
    {
        if (!array_key_exists($key, self::$implementor)) {
            throw $this->createServiceException(sprintf("`%s` File Implementor is not allowed.", $key));
        }
        return $this->createService(self::$implementor[$key]);
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

    protected function getUploadFileDao()
    {
        return $this->createDao('File.UploadFileDao');
    }

}