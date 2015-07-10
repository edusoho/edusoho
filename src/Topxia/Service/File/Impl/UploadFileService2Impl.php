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
        'local'=>'File.LocalFileImplementor',
        'cloud' => 'File.CloudFileImplementor',
    );

    public function initUpload($params)
    {
    	$user = $this->getCurrentUser();

    	$uploadfile = array();
    	$uploadfile['filename'] = empty($params['fileName']) ? '' : $params['fileName'];
    	$uploadfile['size'] = empty($params['fileSize']) ? 0 : $params['fileSize'];
    	$uploadfile['status'] = 'uploading';
    	$uploadfile['targetId'] = $params['targetId'];
    	$uploadfile['targetType'] = $params['targetType'];

    	$uploadfile['hashId'] = uniqid('tmp_');
    	$uploadfile['etag'] = empty($params['fileHash']) ? '' : $params['fileHash'];
    	$uploadfile['convertHash'] = uniqid('tmp_');
    	$uploadfile['storage'] = 'cloud';

    	$uploadfile['updatedUserId'] = $user['id'];
    	$uploadfile['updatedTime'] = time();

    	$uploadfile['createdUserId'] = $user['id'];
    	$uploadfile['createdTime'] = time();

    	$uploadfile = $this->getUploadFileDao()->addFile($uploadfile);

    	$uploadfile = $this->getUploadFileDao()->updateFile($uploadfile['id'], array('globalId' => $uploadfile['id']));

    	return $uploadfile;
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
        return $this->getFileImplementor($storage);
    }

    protected function createFileImplementor($key)
    {
        if (!array_key_exists($key, self::$implementor)) {
            throw $this->createServiceException(sprintf("`%s` File Implementor is not allowed.", $key));
        }
        return $this->createService(self::$implementor[$key]);
    }

    protected function getUploadFileDao()
    {
        return $this->createDao('File.UploadFileDao');
    }

}