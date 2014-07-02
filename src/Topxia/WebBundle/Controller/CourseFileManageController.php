<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Common\Paginator;
use Topxia\Service\Util\CloudClientFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class CourseFileManageController extends BaseController
{

    public function indexAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $type = $request->query->get('type');
        $type = in_array($type, array('courselesson', 'coursematerial')) ? $type : 'courselesson';

        $conditions = array(
            'targetType'=> $type,
            'targetId'=>$course['id']
        );

        $paginator = new Paginator(
            $request,
            $this->getUploadFileService()->searchFileCount($conditions),
            20
        );

        $courseLessons = $this->getUploadFileService()->searchFiles(
            $conditions,
            'latestCreated',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courseLessons, 'updatedUserId'));

        return $this->render('TopxiaWebBundle:CourseFileManage:index.html.twig', array(
            'type' => $type,
            'course' => $course,
            'courseLessons' => $courseLessons,
            'users' => ArrayToolkit::index($users, 'id'),
            'paginator' => $paginator
        ));
    }

    public function showAction(Request $request, $id, $fileId)
    {

        $course = $this->getCourseService()->tryManageCourse($id);

        $file = $this->getUploadFileService()->getFile($fileId);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if ($file['targetType'] == 'courselesson') {
            return $this->forward('TopxiaWebBundle:CourseLesson:file', array('fileId' => $file['id'], 'isDownload' => true));
        } else if ($file['targetType'] == 'coursematerial') {
            if ($file['storage'] == 'cloud') {
                $factory = new CloudClientFactory();
                $client = $factory->createClient();
                $client->download($client->getBucket(), $file['hashId'], 3600, $file['filename']);
            } else {
                return $this->createPrivateFileDownloadResponse($request, $file);
            }
        }

        throw $this->createNotFoundException();
    }

    public function convertAction(Request $request, $id, $fileId)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $file = $this->getUploadFileService()->getFile($fileId);
        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if ($file['convertStatus'] != 'error') {
            return $this->createJsonResponse(array('status' => 'error', 'message' => '只有转换失败的文件，才能重新转换！'));
        }

        if (!in_array($file['type'], array('video', 'ppt'))) {
            return $this->createJsonResponse(array('status' => 'error', 'message' => '只有视频、PPT文件，才能重新转换！'));
        }

        $convertKey = substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 12);

        $factory = new CloudClientFactory();
        $client = $factory->createClient();

        if ($file['type'] == 'video') {
            $commands = array_keys($client->getVideoConvertCommands());
            $result = $client->convertVideo(
                $client->getBucket(), 
                $file['hashId'], 
                implode(';', $commands), 
                $this->generateUrl('uploadfile_cloud_convert_callback', array('key' => $convertKey), true)
            );
        } elseif ($file['type'] == 'ppt') {
            $commands = array_keys($client->getPPTConvertCommands());
            $result = $client->convertVideo(
                $client->getBucket(), 
                $file['hashId'], 
                implode(';', $commands), 
                $this->generateUrl('uploadfile_cloud_convert_callback', array('key' => $convertKey, 'twoStep' => '1'), true)
            );
        }

        if (empty($result['persistentId'])) {
            return $this->createJsonResponse(array('status' => 'error', 'message' => '文件转换请求失败，请重试！'));
        }

        $convertHash = "{$result['persistentId']}:{$convertKey}";

        $this->getUploadFileService()->setFileConverting($file['id'], $convertHash);

        return $this->createJsonResponse(array('status' => 'ok'));
    }


    public function uploadCourseFilesAction(Request $request, $id, $targetType)
    {
        $course = $this->getCourseService()->tryManageCourse($id);
        $storageSetting = $this->getSettingService()->get('storage', array());
        return $this->render('TopxiaWebBundle:CourseFileManage:modal-upload-course-files.html.twig', array(
            'course' => $course,
            'storageSetting' => $storageSetting,
            'targetType' => $targetType,
            'targetId'=>$course['id'],
        ));
    }

    public function deleteCourseFilesAction(Request $request, $id, $type)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $ids = $request->request->get('ids', array());

        $this->getUploadFileService()->deleteFiles($ids);


        return $this->createJsonResponse(true);
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    private function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    private function createPrivateFileDownloadResponse(Request $request, $file)
    {

        $response = BinaryFileResponse::create($file['fullpath'], 200, array(), false);
        $response->trustXSendfileTypeHeader();

        $file['filename'] = urlencode($file['filename']);
        if (preg_match("/MSIE/i", $request->headers->get('User-Agent'))) {
            $response->headers->set('Content-Disposition', 'attachment; filename="'.$file['filename'].'"');
        } else {
            $response->headers->set('Content-Disposition', "attachment; filename*=UTF-8''".$file['filename']);
        }

        $mimeType = FileToolkit::getMimeTypeByExtension($file['ext']);
        if ($mimeType) {
            $response->headers->set('Content-Type', $mimeType);
        }

        return $response;
    }

}