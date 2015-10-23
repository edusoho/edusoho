<?php
namespace Topxia\Service\File\Impl;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Topxia\Common\FileToolkit;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\File\FileImplementor;
use Topxia\Service\Util\CloudClientFactory;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class CloudFileImplementorImpl extends BaseService implements FileImplementor
{   

    private  $cloudClient;

	public function getFile($file)
	{
       $file['convertParams'] = $this->decodeMetas($file['convertParams']);
       $file['metas'] = $this->decodeMetas($file['metas']);
       $file['metas2'] = $this->decodeMetas($file['metas2']);
	   // $file['path'] = $this->getCloudClient()->getFileUrl($file['hashId'],$file['targetId'],$file['targetType']);
       return $file;
	}

    public function addFile($targetType, $targetId, array $fileInfo=array(), UploadedFile $originalFile=null)
    {
        if (!ArrayToolkit::requireds($fileInfo, array('filename','key', 'size'))) {
            throw $this->createServiceException('参数缺失，添加用户文件失败!');
        }

        $uploadFile = array();
        $uploadFile['targetId'] = $targetId;
        $uploadFile['targetType'] = $targetType; 
        $uploadFile['hashId'] = $fileInfo['key'];
        $uploadFile['filename'] = $fileInfo['filename'];
        $uploadFile['ext'] = pathinfo($uploadFile['filename'], PATHINFO_EXTENSION);
        $uploadFile['size'] = (int) $fileInfo['size'];
        $uploadFile['etag'] = empty($fileInfo['etag']) ? '' : $fileInfo['etag'];
        $uploadFile['length'] = empty($fileInfo['length']) ? 0 : intval($fileInfo['length']);

        $uploadFile['metas'] = $this->encodeMetas(empty($fileInfo['metas']) ? array() : $fileInfo['metas']);    
        $uploadFile['metas2'] = $this->encodeMetas(empty($fileInfo['metas2']) ? array() : $fileInfo['metas2']);

        if ($fileInfo['lazyConvert']) {
            $fileInfo['convertHash'] = "lazy-{$uploadFile['hashId']}";
        }
       
        if (empty($fileInfo['convertHash'])) {
            $uploadFile['convertHash'] = "ch-{$uploadFile['hashId']}";
            $uploadFile['convertStatus'] = 'none';
            $uploadFile['convertParams'] = '';
        } else if('document' == FileToolkit::getFileTypeByExtension($uploadFile['ext'])) {

            $uploadFile['convertHash'] = "{$fileInfo['convertHash']}";
            $uploadFile['convertStatus'] = 'none';
            $uploadFile['convertParams'] = $fileInfo['convertParams'];
            
        }else{
            $uploadFile['convertHash'] = "{$fileInfo['convertHash']}";
            $uploadFile['convertStatus'] = 'waiting';
            $uploadFile['convertParams'] = $fileInfo['convertParams'];
        }


        $uploadFile['type'] = FileToolkit::getFileTypeByExtension($uploadFile['ext']);
        $uploadFile['canDownload'] = empty($uploadFile['canDownload']) ? 0 : 1;
        $uploadFile['storage'] = 'cloud';
        $uploadFile['createdUserId'] = $this->getCurrentUser()->id;
        $uploadFile['updatedUserId'] = $uploadFile['createdUserId'];
        $uploadFile['updatedTime'] = $uploadFile['createdTime'] = time();
    
        return $uploadFile; 
    }

    public function saveConvertResult($file, array $result = array())
    {
        if (empty($result['id'])) {
            throw new \RuntimeException('数据中id不能为空');
        }

        if ($result['code'] != 0) {
            $file['convertStatus'] = 'error';
            goto return_result;
        }

        if (empty($file['convertParams']['convertor'])) {
            $file['convertStatus'] = 'error';
            goto return_result;
        }

        $convertor = $this->getConvertor($file['convertParams']['convertor']);
        
        $file = $convertor->saveConvertResult($file, $result);
        
        return_result:
        return $file;
    }

    public function convertFile($file, $status, $result=null, $callback = null)
    {
        if ($status == 'doing') {
            $file['metas2'] = array();
            $file['convertStatus'] = 'doing';
            if ($file['type'] == 'ppt') {
                $cmds = $this->getCloudClient()->getPPTConvertCommands();
                foreach ($result as $item) {
                    $type = empty($cmds[$item['cmd']]) ? null : $cmds[$item['cmd']];
                    if (empty($type)) {
                        continue;
                    }

                    $file['metas2'][$type] = array('type' => $type, 'cmd' => $item['cmd'], 'key' => $item['key']);
                    if ($callback) {
                        $result = $this->getCloudClient()->convertPPT($item['key'], $callback);
                    } else {
                        $result = $this->getCloudClient()->convertPPT($item['key']);
                    }
                    $file['metas2']['length'] = empty($result['length']) ? 0 : $result['length'];
                    $file['metas2']['imagePrefix'] = empty($result['imagePrefix']) ? '' : $result['imagePrefix'];
                }

                if (empty($file['metas2']['length'])) {
                    $file['convertStatus'] = 'error';
                }
            }

        } else if ($status == 'success') {

            if ($file['type'] == 'video') {
                $cmds = $this->getCloudClient()->getVideoConvertCommands();
            } elseif ($file['type'] == 'audio') {
                $cmds = $this->getCloudClient()->getAudioConvertCommands();
            } else {
                $cmds = null;
            }

            if ($cmds) {
                $file['metas2'] = array();
                foreach ($result as $item) {
                    $type = empty($cmds[$item['cmd']]) ? null : $cmds[$item['cmd']];
                    if (empty($type)) {
                        continue;
                    }

                    if ($item['code'] != 0) {
                        continue;
                    }

                    if (empty($item['key'])) {
                        continue;
                    }

                    $file['metas2'][$type] = array('type' => $type, 'cmd' => $item['cmd'], 'key' => $item['key']);
                }

                if (empty($file['metas2'])) {
                    $file['convertStatus'] = 'error';
                } else {
                    $file['convertStatus'] = 'success';
                }
                
            } else {
                $file['convertStatus'] = 'success';
            }

        } else {
            $file['convertStatus'] = $status;
        }

        $file['metas2'] = $this->encodeMetas(empty($file['metas2']) ? array() : $file['metas2']);

        return $file;
    }

    public function deleteFile($file)
    {
        $keys = array($file['hashId']);
        $keyPrefixs = array();

        foreach (array('sd', 'hd', 'shd') as $key) {
            if (empty($file['metas2'][$key]) || empty($file['metas2'][$key]['key'])) {
                continue ;
            }

            // 防错
            if (strlen($file['metas2'][$key]['key']) < 5) {
                continue;
            }

            $keyPrefixs[] = $file['metas2'][$key]['key'];
        }

        if (!empty($file['metas2']['imagePrefix']) && (strlen($file['metas2']['imagePrefix']) > 5)) {
            $keyPrefixs[] = $file['metas2']['imagePrefix'];
        }

        if (!empty($file['metas2']['thumb'])) {
            $keys[] = $file['metas2']['thumb'];
        }

        if (!empty($file['metas2']['pdf']) && !empty($file['metas2']['pdf']['key'])) {
            $keys[] = $file['metas2']['pdf']['key'];
        }

        if (!empty($file['metas2']['swf']) && !empty($file['metas2']['swf']['key'])) {
            $keys[] = $file['metas2']['swf']['key'];
        }

        $result1 = $this->getCloudClient()->deleteFilesByKeys('private', $keys);
        if ($result1['status'] !== 'ok') {
            return false;
        }

        if (!empty($keyPrefixs)) {
            if (!empty($file['convertParams']['convertor']) && $file['convertParams']['convertor'] == 'HLSEncryptedVideo') {
                $result2 = $this->getCloudClient()->deleteFilesByPrefixs('public', $keyPrefixs);
            } else {
                $result2 = $this->getCloudClient()->deleteFilesByPrefixs('private', $keyPrefixs);
            }

            if ($result2['status'] !== 'ok') {
                return false;
            }
        }

        return true;
    }

    public function makeUploadParams($rawParams)
    {
        if (!empty($rawParams['convertor'])) {
            $convertor = $this->getConvertor($rawParams['convertor']);

            $rawUploadParams = array(
                'convertor' => $rawParams['convertor'],
                'convertCallback' => $rawParams['convertCallback'],
                'convertParams' => $convertor->getCovertParams($rawParams),
                'duration' => empty($rawParams['duration']) ? 18000 : $rawParams['duration'],
                'user' => empty($rawParams['user']) ? 0 : $rawParams['user'],
            );

        } else {
            $rawUploadParams = array(
                'convertor' => null,
                'convertCallback' => null,
                'convertParams' => array(),
                'duration' => empty($rawParams['duration']) ? 18000 : $rawParams['duration'],
                'user' => empty($rawParams['user']) ? 0 : $rawParams['user'],
            );
        }
     
        $tokenAndUrl = $this->getCloudClient()->makeUploadParams($rawUploadParams);
 
        $key = null;
        if (!empty($rawParams['key'])) {
            $key = $rawParams['key'];
        }

        if (!empty($rawParams['targetType']) && isset($rawParams['targetId'])) {
            $keySuffix = date('Ymdhis') . '-' . substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 16);
            $key = "{$rawParams['targetType']}-{$rawParams['targetId']}/{$keySuffix}";
        }
        
        if (empty($key)) {
            throw $this->createServiceException("key error.");
        }

        $params = array();
        $params['storage'] = 'cloud';
        $params['url'] = $tokenAndUrl['url'];
        $params['postParams'] = array();
        $params['postParams']['token'] = $tokenAndUrl['token'];
        $params['postParams']['key'] = $key;
        // $params['postParams']['x:convertKey'] = md5($params['postParams']['key']);
        $params['postParams']['x:convertParams'] = json_encode($rawUploadParams['convertParams']);
       
        return $params;
    }

    public function reconvertFile($file, $convertCallback, $pipeline = null)
    {
        if (empty($file['convertParams'])) {
            return null;
        }

        $params = array(
            'convertCallback' => $convertCallback,
            'convertor' => $file['convertParams']['convertor'],
            'convertParams' => $file['convertParams'],
        );

        if ($file['type'] == 'video') {
            $watermarks = $this->getVideoWatermarkImages();

            $file['convertParams']['hasVideoWatermark'] = empty($watermarks) ? 0 : 1;
            $file['convertParams'] = $this->encodeMetas($file['convertParams']);

            $this->getUploadFileDao()->updateFile($file['id'], array('convertParams'=>$file['convertParams']));
        }


        if ($pipeline) {
            $params['pipeline'] = $pipeline;
        }

        if (($file['type'] == 'video') && $watermarks) {
            $params['convertParams']['videoWatermarkImages'] = $watermarks;
        }
        $result = $this->getCloudClient()->reconvertFile($file['hashId'], $params);

        if (empty($result['persistentId'])) {
            return null;
        }
        return $result['persistentId'];
    }

    public function reconvertOldFile($file, $convertCallback, $pipeline = null)
    {
        if (empty($file['convertParams'])) {
            return null;
        }

        $params = array(
            'convertCallback' => $convertCallback,
            'convertor' => $file['convertParams']['convertor'],
            'convertParams' => $file['convertParams'],
        );

        if ($file['type'] == 'video') {
            $watermarks = $this->getVideoWatermarkImages();

            $file['convertParams']['hasVideoWatermark'] = empty($watermarks) ? 0 : 1;
            $file['convertParams'] = $this->encodeMetas($file['convertParams']);

            $this->getUploadFileDao()->updateFile($file['id'], array('convertParams'=>$file['convertParams']));
        }

        if ($pipeline) {
            $params['pipeline'] = $pipeline;
        }

        if (($file['type'] == 'video') && $watermarks) {
            $params['convertParams']['videoWatermarkImages'] = $watermarks;
        }

        $task = array();
        $task['key'] = $file['hashId'];
        $task['processor'] = 'video';
        $task['directives'] = array(
            'videoQuality' => $params['convertParams']['videoQuality'],
            'audioQuality' => $params['convertParams']['audioQuality'],
            'hlsKey' => $params['convertParams']['hlsKey'],
            'hlsKeyUrl' => $params['convertParams']['hlsKeyUrl'],
        );


        if (!empty($params['convertParams']['videoWatermarkImages'])) {
            $task['directives']['watermarks'] = $params['convertParams']['videoWatermarkImages'];
        }

        $task['callbackUrl'] = $convertCallback;

        $api = CloudAPIFactory::create('root');
        $result = $api->post('/processes', $task);
        if (empty($result['taskNo'])) {
            return null;
        }

        return $result['taskNo'];
    }


    public function getMediaInfo($key, $mediaType) {
        return $this->getCloudClient()->getMediaInfo($key, $mediaType);
    }

    protected function getFileFullName($file)
    {
        $diskDirectory= $this->getFilePath($file['targetType'],$file['targetId']);
        $filename .= "{$file['hashId']}.{$file['ext']}";
        return $diskDirectory.$filename; 
    }

    protected function getVideoWatermarkImages()
    {
        $setting = $this->getSettingService()->get('storage',array());
        if (empty($setting['video_embed_watermark_image']) || ($setting['video_watermark'] != 2)) {
            return array();
        }

        $videoWatermarkImage = $this->getEnvVariable('baseUrl').$this->getKernel()->getParameter('topxia.upload.public_url_path')."/".$setting['video_embed_watermark_image'];
        $pathinfo = pathinfo($videoWatermarkImage);

        $images = array();
        $heighs = array('240', '360', '480', '720', '1080');
        foreach ($heighs as $height) {
            $images[$height] = "{$pathinfo['dirname']}/{$pathinfo['filename']}-{$height}.{$pathinfo['extension']}";
        }
        return $images;
    }





    protected function getFilePath($targetType,$targetId)
    {
        $diskDirectory = $this->getKernel()->getParameter('topxia.disk.local_directory');
        $subDir = DIRECTORY_SEPARATOR.$file['targetType'].DIRECTORY_SEPARATOR;
        $subDir .= "{$file['targetType']}-{$file['targetId']}".DIRECTORY_SEPARATOR;
        return $diskDirectory.$subDir;    	
    }

    protected function encodeMetas($metas)
    {
        if(empty($metas) || !is_array($metas)) {
            $metas = array();
        }
        return json_encode($metas);
    }

    protected function decodeMetas($metas)
    {
        if (empty($metas)) {
            return array();
        }
        return json_decode($metas, true);
    }

    protected function getCloudClient()
    {
        if(empty($this->cloudClient)) {
            $factory = new CloudClientFactory();
            $this->cloudClient = $factory->createClient();
        }
        return $this->cloudClient;
    }

    protected function getConvertor($name)
    {
        $class = __NAMESPACE__ . '\\' .  ucfirst($name) . 'Convertor';
        return new $class($this->getCloudClient(), $this->getKernel()->getParameter('cloud_convertor'));
    }

    protected function getUploadFileDao()
    {
        return $this->createDao('File.UploadFileDao');
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

}

class HLSVideoConvertor
{
    const NAME = 'HLSVideo';

    protected $client;

    protected $config = array();

    public function __construct($client, $config)
    {
        $this->client = $client;
        $this->config = $config[self::NAME];
    }

    public function getCovertParams($params)
    {
        $videoQuality = empty($params['videoQuality']) ? 'low' : $params['videoQuality'];
        $videoDefinitions = $this->config['video'][$videoQuality];

        $audioQuality = empty($params['audioQuality']) ? 'low' : $params['audioQuality'];
        $audioDefinitions = $this->config['audio'][$audioQuality];

        return array(
            'convertor' => self::NAME,
            'segtime' => $this->config['segtime'],
            'videoQuality' => $videoQuality,
            'audioQuality' => $audioQuality,
            'video' => $videoDefinitions,
            'audio' => $audioDefinitions,
        );
    }

    public function saveConvertResult($file, $result)
    {
        $items = (empty($result['items']) || !is_array($result['items'])) ? array() : $result['items'];

        $types = array('sd', 'hd', 'shd');
        $metas = array();
        foreach (array_values($items) as $index => $item) {
            $type = $types[$index];
            $metas[$type] = array(
                'type' => $type,
                'cmd' => $item['cmd'],
                'key' => $item['key'],
            );
        }

        $file['metas2'] = empty($file['metas2']) ? array() : $file['metas2'];
        unset($file['metas2']['sd']);
        unset($file['metas2']['hd']);
        unset($file['metas2']['shd']);
        $file['metas2'] = array_merge($file['metas2'], $metas);
        $file['convertStatus'] = 'success';

        return $file;
    }

}

class HLSEncryptedVideoConvertor extends HLSVideoConvertor
{
    public function getCovertParams($params)
    {
        $params = parent::getCovertParams($params);
        $params['convertor'] = 'HLSEncryptedVideo';
        $params['hlsKeyUrl'] = 'http://hlskey.edusoho.net/placeholder';
        $params['hlsKey'] = $this->generateKey(16);
        return $params;
    }

    public function saveConvertResult($file, $result)
    {
        $file = parent::saveConvertResult($file, $result);

        $moves = array(
            array("public:{$file['hashId']}", "private:{$file['hashId']}")
        );
        $result = $this->client->moveFiles($moves);

        $file['metas2']['protectSourceFile'] = empty($result['success_count']) ? 0 : $result['success_count'];

        return $file;
    }

    protected function generateKey ($length = 0 )
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        $key = '';
        for ( $i = 0; $i < 16; $i++ ) {
            $key .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        
        return $key;
    }

}

class AudioConvertor
{
    const NAME = 'audio';

    protected $client;

    protected $config = array();

    public function __construct($client, $config)
    {

    }

    public function saveConvertResult($file, $result)
    {

    }

    public function getCovertParams($params)
    {
        return array(
            'convertor' => self::NAME,
            'shd' => 'mp3',
        );
    }
}

class DocumentConvertor
{
    const NAME = 'document';

    public function saveConvertResult($file, $result)
    {   
        $metas['thumb'] = $result['thumb'];
        $metas['pdf'] = $result['pdf'];
        $metas['swf'] = $result['swf'];
        
        $file['metas2'] = empty($file['metas2']) ? array() : $file['metas2'];
        $file['metas2'] = array_merge($file['metas2'], $metas);
        $file['convertStatus'] = 'success';

        return $file;
    }

    public function getCovertParams($params)
    {
        return array(
            'convertor' => self::NAME,
        );
    }
}

class PptConvertor
{
    const NAME = 'ppt';

    protected $client;

    protected $config = array();

    public function __construct($client, $config)
    {
        $this->client = $client;
        $this->config = $config[self::NAME];
    }

    public function getCovertParams($params)
    {
        $params = array('convertor' => self::NAME,);
        return array_merge($params, $this->config);
    }

    public function saveConvertResult($file, $result)
    {

        if (!empty($result['nextConvertCallbackUrl'])) {
            $items = (empty($result['items']) || !is_array($result['items'])) ? array() : $result['items'];

            $types = array('pdf');
            $metas = array();
            foreach (array_values($items) as $index => $item) {
                $type = $types[$index];
                $metas[$type] = array(
                    'type' => $type,
                    'cmd' => $item['cmd'],
                    'key' => $item['key'],
                );
            }

            if(isset($result['type']) && isset($result['type']) =="ppt" ){
  
                $metas['length'] = empty($result['length']) ? 0 : $result['length'];
 
                $metas['imagePrefix'] = empty($result['imagePrefix']) ? '' : $result['imagePrefix'];

                $file['metas2'] = empty($file['metas2']) ? array() : $file['metas2'];
    
                $file['metas2'] = array_merge($file['metas2'], $metas);
    
                $file['convertStatus'] = 'success';
   
                return $file;

            }

            $result = $this->client->convertPPT($metas['pdf']['key'], $result['nextConvertCallbackUrl']);

            $metas['length'] = empty($result['length']) ? 0 : $result['length'];
            $metas['imagePrefix'] = empty($result['imagePrefix']) ? '' : $result['imagePrefix'];

            $file['metas2'] = empty($file['metas2']) ? array() : $file['metas2'];
            $file['metas2'] = array_merge($file['metas2'], $metas);
            $file['convertStatus'] = 'doing';
        } else {
            $file['convertStatus'] = 'success';
        }

        return $file;
    }
}