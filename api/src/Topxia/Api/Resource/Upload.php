<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Upload extends BaseResource
{
    public function post(Application $app, Request $request, $group)
    {
        if ($request->getMethod() != 'POST') {
            return $this->error('404', 'only allow post!');
        }
        try {
            $file = $request->files->get('file');
            if (empty($file)) {
                return $this->error('404', '没有添加上传文件!');
            }
            $record = $this->getFileService()->uploadFile($group, $file);
            $url = $this->getFileUrl($record['uri']);
        } catch (\Exception $e) {
            return $this->error('500', '上传文件失败!');
        }

        return array(
                'code' => '200',
                'message' => $url
        );
    }

    protected function getFileService()
    {
        return $this->getServiceKernel()->createService('Content:FileService');
    }

    public function filter($res)
    {
        return $res;
    }
}
