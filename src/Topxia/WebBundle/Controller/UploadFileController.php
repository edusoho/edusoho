<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\FileToolkit;
use Topxia\Service\User\CurrentUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
        $targetId   = $request->query->get('targetId');

        $originalFile = $this->get('request')->files->get('file');

        $this->getUploadFileService()->moveFile($targetType, $targetId, $originalFile, $token['data']);

        return $this->createJsonResponse($token['data']);
    }

    public function downloadAction(Request $request, $fileId)
    {
        $file = $this->getUploadFileService()->getFile($fileId);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        $this->getServiceKernel()->createService("System.LogService")->info('upload_file', 'download', "文件Id #{$fileId}");

        if ($file['storage'] == 'cloud') {
            return $this->downloadCloudFile($file);
        } else {
            return $this->downloadLocalFile($request, $file);
        }
    }

    protected function downloadCloudFile($file)
    {
        $file = $this->getUploadFileService()->getDownloadMetas($file['id']);
        return $this->redirect($file['url']);
    }

    protected function downloadLocalFile(Request $request, $file)
    {
        $response = BinaryFileResponse::create($file['fullpath'], 200, array(), false);
        $response->trustXSendfileTypeHeader();
        $file['filename'] = urlencode($file['filename']);

        if (preg_match("/MSIE/i", $request->headers->get('User-Agent'))) {
            $response->headers->set('Content-Disposition', 'attachment; filename="'.$file['filename'].'"');
        } else {
            $response->headers->set('Content-Disposition', 'attachment; filename*=UTF-8 "'.$file['filename'].'"');
        }

        $mimeType = FileToolkit::getMimeTypeByExtension($file['ext']);

        if ($mimeType) {
            $response->headers->set('Content-Type', $mimeType);
        }

        return $response;
    }

    public function browserAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher() && !$user->isAdmin()) {
            throw $this->createAccessDeniedException('您无权查看此页面！');
        }

        $conditions = $request->query->all();

        $conditions['currentUserId'] = $user['id'];
        $conditions['noTargetType']  = 'attachment';
        if (isset($conditions['keyword'])) {
            $conditions['filename'] = $conditions['keyword'];
            unset($conditions['keyword']);
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getUploadFileService()->searchFileCount($conditions),
            20
        );

        $files = $this->getUploadFileService()->searchFiles(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->createFilesJsonResponse($files, $paginator);
    }

    public function browsersAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher() && !$user->isAdmin()) {
            throw $this->createAccessDeniedException('您无权查看此页面！');
        }

        $conditions = $request->query->all();

        if (array_key_exists('targetId', $conditions) && !empty($conditions['targetId'])) {
            $course = $this->getCourseService()->getCourse($conditions['targetId']);

            if ($course['parentId'] > 0 && $course['locked'] == 1) {
                $conditions['targetId'] = $course['parentId'];
            }
        }

        $files = $this->getUploadFileService()->searchFiles($conditions, array('updatedTime', 'DESC'), 0, 10000);

        return $this->createFilesJsonResponse($files);
    }

    public function paramsAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $params = $request->query->all();

        $params['user']             = $user->id;
        $params['defaultUploadUrl'] = $this->generateUrl('uploadfile_upload', array('targetType' => $params['targetType'], 'targetId' => $params['targetId'] ?: '0'));

        if (empty($params['lazyConvert'])) {
            $params['convertCallback'] = $this->generateUrl('uploadfile_cloud_convert_callback2', array(), true);
        } else {
            $params['convertCallback'] = null;
        }

        $params = $this->getUploadFileService()->makeUploadParams($params);

        return $this->createJsonResponse($params);
    }

    protected function cloudCallBack(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $fileInfo = $request->request->all();

        $targetType              = $request->query->get('targetType');
        $targetId                = $request->query->get('targetId');
        $lazyConvert             = $request->query->get('lazyConvert') ? true : false;
        $fileInfo['lazyConvert'] = $lazyConvert;

        if ($targetType == 'headLeader') {
            $storage = $this->getSettingService()->get('storage');
            unset($storage['headLeader']);
            $this->getSettingService()->set('storage', $storage);

            $file = $this->getUploadFileService()->getFileByTargetType($targetType);

            if (!empty($file) && array_key_exists('id', $file)) {
                $this->getUploadFileService()->deleteFile($file['id']);
            }
        }

        $file = $this->getUploadFileService()->addFile($targetType, $targetId, $fileInfo, 'cloud');

        if ($lazyConvert && $file['type'] != "document" && $targetType != 'coursematerial') {
            $this->getUploadFileService()->reconvertFile($file['id'],
                array(
                    'callback' => $this->generateUrl('uploadfile_cloud_convert_callback2', array(), true)
                )
            );
        }

        $this->getUploadFileService()->syncFile($file);
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

        if (empty($file)) {
            $result = array(
                "error" => "文件不存在"
            );

            return $this->createJsonResponse($result);
        }

        return $this->createJsonResponse($file['metas2']);
    }

    protected function cloudConvertCallback2(Request $request)
    {
        $result = $request->getContent();
        $result = preg_replace_callback(
            "(\\\\x([0-9a-f]{2}))i",
            function ($a) {
                return chr(hexdec($a[1]));
            },
            $result
        );

        $this->getLogService()->info('upload_file', 'cloud_convert_callback', "文件云处理回调", array('result' => $result));
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
            return;
        }

        $file = $this->getUploadFileService()->saveConvertResult($file['id'], $result);

        if (in_array($file['convertStatus'], array('success', 'error'))) {
            $this->getNotificationService()->notify($file['createdUserId'], 'cloud-file-converted', array(
                'file' => $file
            ));
        }

        $this->getUploadFileService()->syncFile($file);
        return $file;
    }

    public function cloudConvertCallback3Action(Request $request)
    {
        $result = $request->getContent();

        $result = preg_replace_callback(
            "(\\\\x([0-9a-f]{2}))i",
            function ($a) {
                return chr(hexdec($a[1]));
            },
            $result
        );

        $this->getLogService()->info('upload_file', 'cloud_convert_callback3', "文件云处理回调", array('result' => $result));
        $result = json_decode($result, true);
        $result = array_merge($request->query->all(), $result);

        if (empty($result['id'])) {
            throw new \RuntimeException('数据中id不能为空');
        }

        if ($result['code'] != 0) {
            $this->getLogService()->error('upload_file', 'cloud_convert_error', "文件云处理失败", array('result' => $result));

            return $this->createJsonResponse(true);
        }

        $file = $this->getUploadFileService()->getFileByConvertHash($result['id']);

        if (empty($file)) {
            $this->getLogService()->error('upload_file', 'cloud_convert_error', "文件云处理失败，文件记录不存在", array('result' => $result));
            $result = array(
                "error" => "文件不存在"
            );

            return $this->createJsonResponse($result);
        }

        $file = $this->getUploadFileService()->saveConvertResult3($file['id'], $result);
        $this->getUploadFileService()->syncFile($file);
        return $this->createJsonResponse($file['metas2']);
    }

    public function cloudConvertCallbackAction(Request $request)
    {
        $data = $request->getContent();

        $this->getLogService()->info('upload_file', 'cloud_convert_callback', "文件云处理回调", array('content' => $data));

        $key     = $request->query->get('key');
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

        $items = (empty($data['items']) || !is_array($data['items'])) ? array() : $data['items'];

        $status = $request->query->get('twoStep', false) ? 'doing' : 'success';

        if ($status == 'doing') {
            $callback = $this->generateUrl('uploadfile_cloud_convert_callback', array('key' => $key, 'fullKey' => $hash), true);
            $file     = $this->getUploadFileService()->convertFile($file['id'], $status, $data['items'], $callback);
        } else {
            $file = $this->getUploadFileService()->convertFile($file['id'], $status, $data['items']);
        }

        if (in_array($file['convertStatus'], array('success', 'error'))) {
            $this->getNotificationService()->notify($file['createdUserId'], 'cloud-file-converted', array(
                'file' => $file
            ));
        }

        $this->getUploadFileService()->syncFile($file);
        return $this->createJsonResponse($file['metas2']);
    }

    public function getHeadLeaderHlsKeyAction(Request $request)
    {
        $file          = $this->getUploadFileService()->getFileByTargetType('headLeader');
        $convertParams = json_decode($file['convertParams'], true);

        return new Response($convertParams['hlsKey']);
    }

    public function getMediaInfoAction(Request $request, $type)
    {
        $key  = $request->query->get('key');
        $info = $this->getUploadFileService()->getMediaInfo($key, $type);

        return $this->createJsonResponse($info['format']['duration']);
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getUploadFileService()
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

    protected function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }

    protected function getMaterialService()
    {
        return $this->getServiceKernel()->createService('Course.MaterialService');
    }

    protected function createFilesJsonResponse($files, $paginator = null)
    {
        foreach ($files as &$file) {
            $file['updatedTime'] = $file['updatedTime'] ? $file['updatedTime'] : $file['createdTime'];
            $file['updatedTime'] = date('Y-m-d H:i', $file['updatedTime']);
            $file['fileSize']    = FileToolkit::formatFileSize($file['fileSize']);

            // Delete some file attributes to redunce the json response size
            unset($file['hashId']);
            unset($file['convertHash']);
            unset($file['etag']);
            unset($file['convertParams']);

            unset($file);
        }

        if (!empty($paginator)) {
            $paginator = Paginator::toArray($paginator);
            return $this->createJsonResponse(array(
                'files'     => $files,
                'paginator' => $paginator
            ));
        } else {
            return $this->createJsonResponse($files);
        }
    }
}
