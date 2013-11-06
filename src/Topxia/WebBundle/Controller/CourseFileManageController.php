<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
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

        $updatedUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courseLessons, 'updatedUserId'));
        $createdUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courseLessons, 'createdUserId'));

        return $this->render('TopxiaWebBundle:CourseFileManage:index.html.twig', array(
            'type' => $type,
            'course' => $course,
            'courseLessons' => $courseLessons,
            'updatedUsers' => $updatedUsers,
            'createdUsers' => $createdUsers,
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
            return $this->forward('TopxiaWebBundle:CourseLesson:file', array('fileId' => $file['id']));
        } else if ($file['targetType'] == 'coursematerial') {

            if ($file['storage'] == 'cloud') {
                $factory = new CloudClientFactory();
                $client = $factory->createClient();
                $client->download($client->getBucket(), $file['hashId']);
            } else {
                return $this->createPrivateFileDownloadResponse($file);
            }
        }

        throw $this->createNotFoundException();
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

    public function submitUploadCourseFilesAction(Request $request, $id, $targetType )
    {
        if($targetType == 'coursematerial'){
            return $this->redirect($this->generateUrl('course_manage_files',array('id'=>$id, 'type'=>'coursematerial')));
        } elseif ($targetType == 'courselesson'){
            return $this->redirect($this->generateUrl('course_manage_files',array('id'=>$id, 'type'=>'coursematerial')));
        }
    }

    public function deleteCourseFilesAction(Request $request, $id, $type)
    {
        $ids = $request->request->get('ids', array());
        $course = $this->getCourseService()->tryManageCourse($id);
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

    private function createPrivateFileDownloadResponse($file)
    {
        return BinaryFileResponse::create($file['fullpath'], 200, array(), false, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }

}