<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Topxia\Service\Util\CloudClient;

class DiskController extends BaseController
{

    public function uploadAction(Request $request)
    {
        $token = $request->request->get('token');
        $token = $this->getUserService()->getToken('diskLocalUpload', $token);
        if (empty($token)) {
            throw $this->createAccessDeniedException('上传TOKEN已过期或不存在。');
        }

        $filepath = '/' . $request->request->get('x:filepath', '');

        $file = $this->get('request')->files->get('file');
        $file = $this->getDiskService()->addLocalFile($file, $token['userId'], $filepath);

        return $this->createJsonResponse($file);
    }

    public function uploadCallbackAction (Request $request)
    {
        $data = $request->request->all();
        $data['storage'] = 'cloud';

        $file = $this->getDiskService()->addCloudFile($data);

        return $this->createJsonResponse($file);
    }

    public function browseAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $type = $request->query->get('type');

        $files = $this->getDiskService()->searchFiles(array(
            'userId' => $user['id'],
            'type' => $type,
        ), 'latestUpdated', 0, 1000);

        return $this->createFilesJsonResponse($files);
    }

    public function convertCallbackAction(Request $request)
    {
        $data = $request->getContent();

        $this->getLogService()->info('disk', 'convert_callback', "文件云处理回调:{$data}");

        $key = $request->query->get('key');
        if (empty($key)) {
            throw new \RuntimeException('key不能为空');
        }
        
        $data = json_decode($data, true);
        if (empty($data['id'])) {
            throw new \RuntimeException('数据中id不能为空');
        }

        $hash = "{$data['id']}:{$key}";

        $file = $this->getDiskService()->getFileByConvertHash($hash);
        if (empty($file)) {
            throw new \RuntimeException('文件不存在');
        }

        if ($data['code'] != 0) {
            $this->getDiskService()->changeFileConvertStatus($file['id'], 'error');
            throw new \RuntimeException('转换失败');
        }

        $items = (empty($data['items']) or !is_array($data['items'])) ? array() : $data['items'];
        $file = $this->getDiskService()->setFileFormats($file['id'], $data['items']);

        // @todo refactor
        $lesson = $this->getCourseService()->getLessonByMediaId($file['id']);
        if ($lesson) {
            $this->getNotificationService()->notify($file['userId'], 'cloud-file-converted', array(
                'lessonId' => $lesson['id'],
                'courseId' => $lesson['courseId'],
                'filename' => $file['filename'],
            ));

        }

        return $this->createJsonResponse($file['formats']);
    }

    public function convertStatusAction(Request $request)
    {
        $hash = $request->query->get('hash');
        if (empty($hash)) {
            throw new \RuntimeException('hash不能为空');
        }

        $file = $this->getDiskService()->getFileByConvertHash($hash);
        if (empty($file)) {
            throw $this->createNotFoundException('文件不存在');
        }

        return $this->createJsonResponse(array('status' => $file['convertStatus']));
    }

    private function createFilesJsonResponse($files)
    {
        foreach ($files as &$file) {
            $file['updatedTime'] = date('Y-m-d H:i', $file['updatedTime']);
            $file['size'] = $this->formatFileSize($file['size']);
            unset($file);
        }
        return $this->createJsonResponse($files);
    }

    private function formatFileSize($size)
    {
        $currentValue = $currentUnit = null;
        $unitExps = array('B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3);
        foreach ($unitExps as $unit => $exp) {
            $divisor = pow(1000, $exp);
            $currentUnit = $unit;
            $currentValue = $size / $divisor;
            if ($currentValue < 1000) {
                break;
            }
        }

        return sprintf('%.1f', $currentValue) . $currentUnit;
    }

    private function getDiskService()
    {
        return $this->getServiceKernel()->createService('User.DiskService');
    }

    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}