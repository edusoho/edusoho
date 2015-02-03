<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Service\Util\CloudClientFactory;
use Topxia\Common\StringToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Service\User\CurrentUser;

class UploadFileController extends BaseController
{

    public function uploadAction(Request $request)
    {
        $token = $request->request->get('token');
        $token = $this->getUserService()->getToken('fileupload', $token);
        if (empty($token)) {
            throw $this->createAccessDeniedException('上传TOKEN已过期或不存在。');
        }

        $user = $this->getUserService()->getUser($token['userId']);
        if (empty($user)) {
            throw $this->createAccessDeniedException('上传TOKEN非法。');
        }

        $currentUser = new CurrentUser();
        $this->getServiceKernel()->setCurrentUser($currentUser->fromArray($user));

        $targetType = $request->query->get('targetType');
        $targetId = $request->query->get('targetId');

        $originalFile = $this->get('request')->files->get('file');

        $file = $this->getCourseService()->uploadCourseFile($targetType, $targetId, array(), 'local', $originalFile);
    	return $this->createJsonResponse($file);
    }

    public function browserAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->isTeacher() && !$user->isAdmin()) {
            throw $this->createAccessDeniedException('您无权查看此页面！');
        }

        $conditions = $request->query->all();

        $materialLibApp = $this->getAppService()->findInstallApp('MaterialLib');

        if(!empty($materialLibApp)){
            $conditions['currentUserId'] = $user['id'];
        }
        
        $files = $this->getUploadFileService()->searchFiles($conditions, 'latestUpdated', 0, 10000);
        
        return $this->createFilesJsonResponse($files);
    }

     public function browsersAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->isTeacher() && !$user->isAdmin()) {
            throw $this->createAccessDeniedException('您无权查看此页面！');
        }

        $conditions = $request->query->all();

        $files = $this->getUploadFileService()->searchFiles($conditions, 'latestUpdated', 0, 10000);
        
        return $this->createFilesJsonResponse($files);
    }
    
    public function paramsAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $params = $request->query->all();

        $params['user'] = $user->id;
        $params['defaultUploadUrl'] = $this->generateUrl('uploadfile_upload', array('targetType' => $params['targetType'], 'targetId' => $params['targetId'] ?: '0' ));

        if (empty($params['lazyConvert'])) {
            $params['convertCallback'] = $this->generateUrl('uploadfile_cloud_convert_callback2', array(), true);
        } else {
            $params['convertCallback'] = null;
        }
        
        $params = $this->getUploadFileService()->makeUploadParams($params);
  
    
        return $this->createJsonResponse($params);
    }

    private function cloudCallBack(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $fileInfo = $request->request->all();
      
        $targetType = $request->query->get('targetType');
        $targetId = $request->query->get('targetId');
        $lazyConvert = $request->query->get('lazyConvert') ? true : false;
        $fileInfo['lazyConvert'] = $lazyConvert;

        if($targetType == 'headLeader'){
            $storage = $this->getSettingService()->get('storage');
            unset($storage['headLeader']);
            $this->getSettingService()->set('storage', $storage);

            $file = $this->getUploadFileService()->getFileByTargetType($targetType);
            if(!empty($file) && array_key_exists('id', $file)){
                $this->getUploadFileService()->deleteFile($file['id']);
            }
        }

        $file = $this->getUploadFileService()->addFile($targetType, $targetId, $fileInfo, 'cloud');

        if ($lazyConvert && $file['type']!="document") {
        
            $convertHash = $this->getUploadFileService()->reconvertFile(
                $file['id'],
                $this->generateUrl('uploadfile_cloud_convert_callback2', array(), true)
            );
        }

        return $file;
    }

    public function cloudCallbackAction(Request $request)
    {
        $file = $this->cloudCallBack($request);
        return $this->createJsonResponse($file);
    }

    public function cloudConvertCallback2Action(Request $request)
    {
        $file = $this->cloudConvertCallback2($request);    

        return $this->createJsonResponse($file['metas2']);
    }

    private function cloudConvertCallback2(Request $request)
    {
        $result = $request->getContent();
        $result = preg_replace_callback(
          "(\\\\x([0-9a-f]{2}))i",
          function($a) {return chr(hexdec($a[1]));},
          $result
        );

        $this->getLogService()->info('uploadfile', 'cloud_convert_callback', "文件云处理回调", array('result' => $result));
        $result = json_decode($result, true);
        $result = array_merge($request->query->all(), $result);
        if (empty($result['id'])) {
            throw new \RuntimeException('数据中id不能为空');
        }

        if (!empty($result['convertHash'])) {
            $file = $this->getUploadFileService()->getFileByConvertHash($result['convertHash']);
        } else {
            $file = $this->getUploadFileService()->getFileByConvertHash($result['id']);
            if ($file && $file['type'] == 'ppt') {
                $result['nextConvertCallbackUrl'] = $this->generateUrl('uploadfile_cloud_convert_callback2', array('convertHash' => $result['id']), true);
            }
        }

        if (empty($file)) {
            throw new \RuntimeException('文件不存在');
        }
        $file = $this->getUploadFileService()->saveConvertResult($file['id'], $result);

        if (in_array($file['convertStatus'], array('success', 'error'))) {
            $this->getNotificationService()->notify($file['createdUserId'], 'cloud-file-converted', array(
                'file' => $file,
            ));
        }
        return $file;
    }

    public function cloudConvertCallback3Action(Request $request)
    {
        $result = $request->getContent();

        $result = preg_replace_callback(
          "(\\\\x([0-9a-f]{2}))i",
          function($a) {return chr(hexdec($a[1]));},
          $result
        );

        $this->getLogService()->info('uploadfile', 'cloud_convert_callback3', "文件云处理回调", array('result' => $result));
        $result = json_decode($result, true);
        $result = array_merge($request->query->all(), $result);
        if (empty($result['id'])) {
            throw new \RuntimeException('数据中id不能为空');
        }

        if ($result['code'] != 0) {
            $this->getLogService()->error('uploadfile', 'cloud_convert_error', "文件云处理失败", array('result' => $result));
            return $this->createJsonResponse(true);
        }

        $file = $this->getUploadFileService()->getFileByConvertHash($result['id']);
        if (empty($file)) {
            $this->getLogService()->error('uploadfile', 'cloud_convert_error', "文件云处理失败，文件记录不存在", array('result' => $result));
            throw new \RuntimeException('文件不存在');
        }

        $file = $this->getUploadFileService()->saveConvertResult3($file['id'], $result);

        return $this->createJsonResponse($file['metas2']);
    }    

    public function cloudConvertCallbackAction(Request $request)
    {
        $data = $request->getContent();

        $this->getLogService()->info('uploadfile', 'cloud_convert_callback', "文件云处理回调", array('content' => $data));

        $key = $request->query->get('key');
        $fullKey = $request->query->get('fullKey');
        if (empty($key)) {
            throw new \RuntimeException('key不能为空');
        }

        $data = json_decode($data, true);

        if (empty($data['id'])) {
            throw new \RuntimeException('数据中id不能为空');
        }

        if ($fullKey) {
            $hash = $fullKey;
        } else {
            $hash = "{$data['id']}:{$key}";
        }

        $file = $this->getUploadFileService()->getFileByConvertHash($hash);
        if (empty($file)) {
            throw new \RuntimeException('文件不存在');
        }

        if ($data['code'] != 0) {
            $this->getUploadFileService()->convertFile($file['id'], 'error');
            throw new \RuntimeException('转换失败');
        }

        $items = (empty($data['items']) or !is_array($data['items'])) ? array() : $data['items'];

        $status = $request->query->get('twoStep', false) ? 'doing' : 'success';

        if ($status == 'doing') {
            $callback = $this->generateUrl('uploadfile_cloud_convert_callback', array('key' => $key, 'fullKey' => $hash), true);
            $file = $this->getUploadFileService()->convertFile($file['id'], $status, $data['items'], $callback);
        } else {
            $file = $this->getUploadFileService()->convertFile($file['id'], $status, $data['items']);
        }

        if (in_array($file['convertStatus'], array('success', 'error'))) {
            $this->getNotificationService()->notify($file['createdUserId'], 'cloud-file-converted', array(
                'file' => $file,
            ));
        }

        return $this->createJsonResponse($file['metas2']);
    }

    public function getHeadLeaderHlsKeyAction(Request $request)
    {
        $file = $this->getUploadFileService()->getFileByTargetType('headLeader');
        $convertParams = json_decode($file['convertParams'], true);
        return new Response($convertParams['hlsKey']);
    }

    public function getMediaInfoAction(Request $request, $type)
    {
        $key = $request->query->get('key');
        $info = $this->getUploadFileService()->getMediaInfo($key, $type);
        return $this->createJsonResponse($info['format']['duration']);
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    private function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    private function createFilesJsonResponse($files)
    {
        foreach ($files as &$file) {
            $file['updatedTime'] = date('Y-m-d H:i', $file['updatedTime']);
            $file['size'] = FileToolkit::formatFileSize($file['size']);

            // Delete some file attributes to redunce the json response size
            unset($file['hashId']);
            unset($file['convertHash']);
            unset($file['etag']);
            unset($file['convertParams']);

            unset($file);
        }
        return $this->createJsonResponse($files);
    }

}