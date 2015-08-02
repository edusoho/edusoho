<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\WebBundle\Util\UploadToken;
use Topxia\Common\FileToolkit;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Util\UploaderToken;

/**
 * 素材库上传组件控制器
 */
class UploaderController extends BaseController
{

    public function initAction(Request $request)
    {
        $token = $request->query->get('token');
        $parser = new UploaderToken();
        $params = $parser->parse($token);
        if (!$params) {
        	return $this->createJsonResponse(array('error' => '上传授权码不正确，请重试！'));
        }

        var_dump($params);

        $params = array_merge($request->request->all(), $params);

        $result = $this->getUploadFileService()->initUpload($params);

        return $this->createJsonResponse($result);
    }

    public function finishedAction(Request $request)
    {
        $params = $request->request->all();
        $this->getUploadFileService()->finishedUpload($params);
        return $this->createJsonResponse(true);
    }

    public function processedAction(Request $request)
    {
        $params = $request->request->all();

        // $params = json_decode('{"requestId":"C0936728-7F4B-8238-0638-1A63470511AE","data":"{\"m3u8s\":[\"2015\\\/07-23\\\/9477AB53-7C05-0E9C-B4BC-F920A86F4FEE\\\/9477AB53-7C05-0E9C-B4BC-F920A86F4FEE_54\",\"2015\\\/07-23\\\/9477AB53-7C05-0E9C-B4BC-F920A86F4FEE\\\/9477AB53-7C05-0E9C-B4BC-F920A86F4FEE_55\",\"2015\\\/07-23\\\/9477AB53-7C05-0E9C-B4BC-F920A86F4FEE\\\/9477AB53-7C05-0E9C-B4BC-F920A86F4FEE_56\"],\"report\":{\"dispatchTime\":1437591838,\"downloadingCost\":0,\"downloadingFileSize\":11299151,\"metadata\":{\"duration\":\"00:01:08.65\",\"seconds\":68.65,\"start\":\"0.000000\",\"bitrate\":\"1316\",\"vcodec\":\"h264 (High) (avc1 \\\/ 0x31637661)\",\"vformat\":\"yuv420p\",\"resolution\":\"1280x720\",\"width\":\"1280\",\"height\":\"720\",\"vb\":\"1198\",\"fps\":\"20\",\"ab\":\"116\",\"play_time\":68.65,\"size\":11299151},\"convert\":{\"sd\":{\"convertingCost\":6,\"originFileSize\":11299151,\"convertedFileSize\":3756804,\"segmentNum\":7},\"md\":{\"convertingCost\":6,\"originFileSize\":11299151,\"convertedFileSize\":5014524,\"segmentNum\":7},\"hd\":{\"convertingCost\":8,\"originFileSize\":11299151,\"convertedFileSize\":10579700,\"segmentNum\":7}},\"uploadingCost\":0,\"finishedTime\":1437591858}}","globalId":"102"}', true);

        $params['data'] = json_decode($params['data'], true);

        $this->getUploadFileService()->setFileProcessed($params);

        return $this->createJsonResponse(true);
    }

    public function batchUploadAction(Request $request)
    {
        $token = $request->query->get('token');
        $parser = new UploaderToken();
        $params = $parser->parse($token);
        if (!$params) {
            return $this->createJsonResponse(array('error' => '上传授权码不正确，请重试！'));
        }

        $accept = $this->getUploadFileAccept($params['targetType'], $request->query->get('only'));

        return $this->render('TopxiaWebBundle:Uploader:batch-upload-modal.html.twig', array(
            'token' => $token,
            'targetType' => $params['targetType'],
            'accept' => $accept,
        ));
    }

    protected function getUploadFileAccept($targetType, $only = '')
    {
        $targetAcceptTypes = array(
            'courselesson' => array('video', 'audio', 'flash', 'ppt', 'cloud_document'),
            'coursematerial' => array('video', 'audio', 'flash', 'ppt', 'document', 'zip', 'image', 'text'),
            'materiallib' => array('video', 'audio', 'flash', 'ppt', 'document', 'zip', 'image', 'text'),
        );

        $availableAccepts = array(
            'video' => array(
                'extensions' => array('mp4', 'avi', 'flv', 'f4v', 'wmv', 'mov', 'rmvb', 'mkv'),
                'mimeTypes' => array('video/*'),
            ),
            'audio' => array(
                'extensions' => array('mp3'),
                'mimeTypes' => array('audio/*')
            ),
            'flash' => array(
                'extensions' => array('swf'),
                'mimeTypes' => array('application/x-shockwave-flash'),
            ),
            'ppt' => array(
                'extensions' => array('ppt', 'pptx'),
                'mimeTypes' => array('application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'),
            ),
            'cloud_document' => array(
                'extensions' => array('doc', 'docx', 'pdf'),
                'mimeTypes' => array('application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf'),
            ),
            'document' => array(
                'extensions' => array('doc', 'docx', 'pdf', 'xls', 'xlsx', 'wps', 'odt'),
                'mimeTypes' => array('application/vnd.ms-*', 'application/msword', 'application/pdf', 'application/vnd.openxmlformats-officedocument.*'),
            ),
            'zip' => array(
                'extensions' => array('zip', 'rar', 'gz', 'tar', '7z'),
                'mimeTypes' => array('application/zip', 'application/x-rar*', 'application/x-tar', 'application/x-gz*', 'application/x-7z*'),
            ),
            'image' => array(
                'extensions' => array('jpg', 'jpeg', 'png', 'gif', 'bmp'),
                'mimeTypes' => array('image/*'),
            ),
            'text' => array(
                'extensions' => array('txt', 'html', 'js', 'css'),
                'mimeTypes' => array('text/*'),
            ),
            'all' => array(
                'extensions' => array('*'),
                'mimeTypes' => array('*'),
            )
        );

        $types = array();

        $only = explode(',', $only);
        if ($only && !empty($only[0])) {
            $types = $only;
        } elseif (isset($targetAcceptTypes[$targetType])) {
            $types = $targetAcceptTypes[$targetType];
        } else {
            $types = array('all');
        }

        $accept = array('extensions' => array(), 'mimeTypes' => array());
        foreach ($types as $type) {
            if (isset($availableAccepts[$type])) {
                $accept['extensions'] = array_merge($accept['extensions'], $availableAccepts[$type]['extensions']);
                $accept['mimeTypes'] = array_merge($accept['mimeTypes'], $availableAccepts[$type]['mimeTypes']);
            }
        }

        return $accept;
    }

    public function echoAction(Request $request)
    {
        $this->getUploadFileService()->finishedUpload(1);

        exit();
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService2');
    }



}

