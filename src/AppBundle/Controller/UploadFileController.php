<?php

namespace AppBundle\Controller;

use AppBundle\Common\FileToolkit;
use AppBundle\Common\MessageToolkit;
use AppBundle\Common\Paginator;
use Biz\CloudPlatform\Service\AppService;
use Biz\Content\Service\FileService;
use Biz\Course\Service\CourseService;
use Biz\File\Service\UploadFileService;
use Biz\File\UploadFileException;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\User\Service\NotificationService;
use Biz\User\Service\UserService;
use Biz\User\TokenException;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UploadFileController extends BaseController
{
    public function uploadAction(Request $request)
    {
        if ($request->isMethod('OPTIONS')) {
            // SDK 跨域认证
            $response = $this->createJsonResponse(true);
            $response->headers->set('Access-Control-Allow-Origin', '*');

            return $response;
        }

        $token = $request->request->get('token');
        $token = $this->getUserService()->getToken('fileupload', $token);

        if (empty($token)) {
            $this->createNewException(TokenException::TOKEN_INVALID());
        }

        $user = $this->getUserService()->getUser($token['userId']);

        if (empty($user)) {
            $this->createNewException(TokenException::NOT_MATCH_USER());
        }
        $this->getCurrentUser()->fromArray($user);

        $targetType = $request->query->get('targetType');
        $targetId = $request->query->get('targetId');

        $originalFile = $this->get('request')->files->get('file');

        $this->getUploadFileService()->moveFile($targetType, $targetId, $originalFile, $token['data']);

        $response = $this->createJsonResponse($token['data']);
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }

    public function downloadAction(Request $request, $fileId)
    {
        $ssl = $request->isSecure() ? true : false;
        $file = $this->getUploadFileService()->getFile($fileId);

        if (empty($file)) {
            $this->createNewException(UploadFileException::NOTFOUND_FILE());
        }

        $this->getLogService()->info('upload_file', 'download', "文件Id #{$fileId}");

        if (in_array($file['storage'], ['cloud', 'supplier'])) {
            return $this->downloadCloudFile($request, $file, $ssl);
        } else {
            return $this->downloadLocalFile($request, $file);
        }
    }

    protected function downloadCloudFile(Request $request, $file, $ssl)
    {
        $file = $this->getUploadFileService()->getDownloadMetas($file['id'], $ssl);

        if (!empty($file['error'])) {
            $this->setFlashMessage('danger', MessageToolkit::convertMessageToKey($file['error']));

            return $this->redirect($request->server->get('HTTP_REFERER'));
        }

        return $this->redirect($file['url']);
    }

    protected function downloadLocalFile(Request $request, $file)
    {
        $response = BinaryFileResponse::create($file['fullpath'], 200, [], false);
        $response->trustXSendfileTypeHeader();

        $fileName = urlencode(str_replace(' ', '', $file['filename']));
        $response->headers->set('Content-Disposition', 'attachment; filename='.$fileName."; filename*=UTF-8''".$fileName);

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
            $this->createNewException(UserException::PERMISSION_DENIED());
        }

        $conditions = $request->query->all();

        $conditions['currentUserId'] = $user['id'];

        if ('upload' == $conditions['source']) {
            $conditions['createdUserId'] = $user['id'];
        }

        $conditions['noTargetType'] = 'attachment';
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
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->createFilesJsonResponse($files, $paginator);
    }

    public function browsersAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher() && !$user->isAdmin()) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }

        $conditions = $request->query->all();

        if (array_key_exists('targetId', $conditions) && !empty($conditions['targetId'])) {
            $course = $this->getCourseService()->getCourse($conditions['targetId']);

            if ($course['parentId'] > 0 && 1 == $course['locked']) {
                $conditions['targetId'] = $course['parentId'];
            }
        }

        $files = $this->getUploadFileService()->searchFiles($conditions, ['updatedTime' => 'DESC'], 0, 10000);

        return $this->createFilesJsonResponse($files);
    }

    public function paramsAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $params = $request->query->all();

        $params['user'] = $user->id;
        $params['defaultUploadUrl'] = $this->generateUrl('uploadfile_upload', ['targetType' => $params['targetType'], 'targetId' => $params['targetId'] ?: '0']);

        if (empty($params['lazyConvert'])) {
        } else {
            $params['convertCallback'] = null;
        }

        $params = $this->getUploadFileService()->makeUploadParams($params);

        return $this->createJsonResponse($params);
    }

    public function getHeadLeaderHlsKeyAction(Request $request)
    {
        $file = $this->getUploadFileService()->getFileByTargetType('headLeader');
        $convertParams = json_decode($file['convertParams'], true);

        return new Response($convertParams['hlsKey']);
    }

    protected function createFilesJsonResponse($files, $paginator = null)
    {
        foreach ($files as &$file) {
            $file['updatedTime'] = $file['updatedTime'] ? $file['updatedTime'] : $file['createdTime'];
            $file['updatedTime'] = date('Y-m-d H:i', $file['updatedTime']);
            $file['fileSize'] = FileToolkit::formatFileSize($file['fileSize']);

            // Delete some file attributes to redunce the json response size
            unset($file['hashId']);
            unset($file['convertHash']);
            unset($file['etag']);
            unset($file['convertParams']);

            unset($file);
        }

        if (!empty($paginator)) {
            $paginator = Paginator::toArray($paginator);

            return $this->createJsonResponse([
                'files' => $files,
                'paginator' => $paginator,
            ]);
        } else {
            return $this->createJsonResponse($files);
        }
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->getBiz()->service('System:LogService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return $this->getBiz()->service('User:NotificationService');
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->getBiz()->service('CloudPlatform:AppService');
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->getBiz()->service('Content:FileService');
    }

    /**
     * @return MaterialService
     */
    protected function getMaterialService()
    {
        return $this->getBiz()->service('Course:MaterialService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }
}
