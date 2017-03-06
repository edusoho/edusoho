<?php

namespace AppBundle\Controller;

use Biz\Content\Service\FileService;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Util\UploadToken;
use AppBundle\Common\FileToolkit;
use AppBundle\Common\ArrayToolkit;

class FileController extends BaseController
{
    public function uploadAction(Request $request)
    {
        list($groupCode, $type) = $this->tryUploadFile($request);

        if (!$this->isGroup($groupCode)) {
            return $this->createMessageResponse('error', '参数不正确');
        }

        $file = $request->files->get('file');
        if ($type == 'image') {
            if (!FileToolkit::isImageFile($file)) {
                throw $this->createAccessDeniedException('您上传的不是图片文件，请重新上传。');
            }
        } else {
            throw $this->createAccessDeniedException('上传类型不正确！');
        }

        $record = $this->getFileService()->uploadFile($groupCode, $file);
        $record['url'] = $this->get('web.twig.extension')->getFilePath($record['uri']);
        $request->getSession()->set('fileId', $record['id']);

        return $this->createJsonResponse($record);
    }

    public function cropImgAction(Request $request)
    {
        $options = $request->request->all();
        if (empty($options['group'])) {
            $options['group'] = 'default';
        }

        if (!$this->isGroup($options['group'])) {
            return $this->createMessageResponse('error', '参数不正确');
        }

        $fileId = $request->getSession()->get('fileId');
        if (empty($fileId)) {
            return $this->createMessageResponse('error', '参数不正确');
        }

        $record = $this->getFileService()->getFile($fileId);
        if (empty($record)) {
            return $this->createMessageResponse('error', '文件不存在');
        }
        $parsed = $this->getFileService()->parseFileUri($record['uri']);

        $filePaths = FileToolKit::cropImages($parsed['fullpath'], $options);
        $fields = array();
        foreach ($filePaths as $key => $value) {
            $file = $this->getFileService()->uploadFile($options['group'], new File($value));
            $fields[] = array(
                'type' => $key,
                'id' => $file['id'],
            );
        }

        if (isset($options['deleteOriginFile']) && $options['deleteOriginFile'] == 0) {
            $fields[] = array(
                'type' => 'origin',
                'id' => $record['id'],
            );
        } else {
            $this->getFileService()->deleteFileByUri($record['uri']);
        }

        return $this->createJsonResponse($fields);
    }

    protected function isGroup($group)
    {
        $groups = $this->getFileService()->getAllFileGroups();
        $codes = ArrayToolkit::column($groups, 'code');

        return in_array($group, $codes);
    }

    protected function tryUploadFile($request)
    {
        $token = $request->request->get('token');

        $maker = new UploadToken();
        $token = $maker->parse($token);

        if (empty($token)) {
            throw $this->createAccessDeniedException('上传授权码已过期，请刷新页面后重试！');
        }

        $groupCode = $token['group'];
        if (empty($groupCode)) {
            $groupCode = 'default';
        }

        return array($groupCode, $token['type']);
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->getBiz()->service('Content:FileService');
    }
}
