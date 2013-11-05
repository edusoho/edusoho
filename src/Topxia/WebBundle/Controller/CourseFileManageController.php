<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;

use Topxia\Service\Util\CloudClientFactory;
use Topxia\Common\StringToolkit;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Common\Paginator;


class CourseFileManageController extends BaseController
{

    public function indexAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);
        $conditions = array(
            'targetType'=>'courselesson', 
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
            'course' => $course,
            'courseLessons' => $courseLessons,
            'updatedUsers' => $updatedUsers,
            'createdUsers' => $createdUsers,
            'paginator' => $paginator
        ));
    }

    public function materialAction(Request $request, $id)
    {
        
        $course = $this->getCourseService()->tryManageCourse($id);
        $conditions = array(
            'targetType'=>'coursematerial', 
            'targetId'=>$course['id']
        );

        $paginator = new Paginator(
            $request,
            $this->getUploadFileService()->searchFileCount($conditions),
            20
        );

        $courseMaterials = $this->getUploadFileService()->searchFiles(
            $conditions,
            'latestCreated',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $updatedUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courseMaterials, 'updatedUserId'));
        $createdUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courseMaterials, 'createdUserId'));

        return $this->render('TopxiaWebBundle:CourseFileManage:materials.html.twig', array(
            'course' => $course,
            'courseMaterials' => $courseMaterials,
            'updatedUsers' => $updatedUsers,
            'createdUsers' => $createdUsers,
            'paginator' => $paginator
        ));
    }

    public function uploadCourseLessonAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);
        $storageSetting = $this->getSettingService()->get('storage', array());
        return $this->render('TopxiaWebBundle:CourseFileManage:modal-upload-course-lesson.html.twig', array(
            'course' => $course,
            'storageSetting' => $storageSetting
        ));
    }

    public function uploadCourseMaterialAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);
        $storageSetting = $this->getSettingService()->get('storage', array());
        return $this->render('TopxiaWebBundle:CourseFileManage:modal-upload-course-material.html.twig', array(
            'course' => $course,
            'storageSetting' => $storageSetting
        ));
    }

    public function submitUploadCourseFilesAction(Request $request, $id, $fileType )
    {
        return $this->redirect($this->generateUrl('course_manage_files',array('id'=>$id)));
    }

    public function uploadCourseLessonAsOneAction(Request $request, $id)
    {
        $file = $request->files->get('file');
        $uploadedChunk = $request->request->all();
        $fileName = $this->getUploadedFilename($uploadedChunk, $file);
        $tmpDirectory = $this->getTmpDirectory();
        $filePath = $tmpDirectory . DIRECTORY_SEPARATOR . $fileName;
        $chunk = isset($uploadedChunk["chunk"]) ? intval($uploadedChunk["chunk"]) : 0;
        $chunks = isset($uploadedChunk["chunks"]) ? intval($uploadedChunk["chunks"]) : 0;

        $this->removeOldTmpFiles($filePath, $tmpDirectory);
        $this->openTmpFiles($chunks, $chunk, $filePath, $file);

        // 当为最后一个chunk的时候,可以执行一些操作： 上传文件，更新数据库等等
        if (!$chunks || $chunk == $chunks - 1) {
            // $result = $this->getFileService()->uploadFile('course_private', new File($filePath));
            return $this->createJsonResponse(array('status' => 'ok', 'file' => 'file'));
        } else {
            return $this->createJsonResponse(array('status' => 'uploading', 'message' => '正在上传...'));
        }
    }

    public function uploadCourseMaterialAsOneAction(Request $request, $id)
    {
        // flash 只会上传一次token
        $file = $request->files->get('file');
        $uploadedChunk = $request->request->all();
        $fileName = $this->getUploadedFilename($uploadedChunk, $file);
        $tmpDirectory = $this->getTmpDirectory();
        $filePath = $tmpDirectory . DIRECTORY_SEPARATOR . $fileName;
        $chunk = isset($uploadedChunk["chunk"]) ? intval($uploadedChunk["chunk"]) : 0;
        $chunks = isset($uploadedChunk["chunks"]) ? intval($uploadedChunk["chunks"]) : 0;

        $this->removeOldTmpFiles($filePath, $tmpDirectory);
        $this->openTmpFiles($chunks, $chunk, $filePath, $file);

        // 当为最后一个chunk的时候,可以执行一些操作： 上传文件，更新数据库等等
        if (!$chunks || $chunk == $chunks - 1) {
            // $result = $this->getFileService()->uploadFile('course_private', new File($filePath));
            return $this->createJsonResponse(array('status' => 'ok', 'file' => 'file'));
        } else {
            return $this->createJsonResponse(array('status' => 'uploading', 'message' => '正在上传...'));
        }

    }


    public function deleteCourseFilesAction(Request $request, $id, $type)
    {
        $ids = $request->request->get('ids', array());
        $course = $this->getCourseService()->tryManageCourse($id);
        return $this->createJsonResponse(true);
    }

    private function getTmpDirectory()
    {
        $tmpDirectory = $this->container->getParameter('topxia.upload.public_directory').DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'plupload';
        if (!file_exists($tmpDirectory)) {
            @mkdir($tmpDirectory);
        }

        return $tmpDirectory;
    }

    private function getUploadedFilename($uploadedChunk, $file)
    {
        $fileName = null;
        $fileName = $file->getFilename();
        if ($uploadedChunk) {
            $fileName = $uploadedChunk["name"];
        } else {
            $fileName = uniqid("file_");
        }
        return $fileName;
    }

    private function openTmpFiles($chunks, $chunk, $filePath, $file)
    {
        if (!$outStream = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
            throw new \RuntimeException('输出流打开失败!');
        }

        if (!empty($file)) {
            if ($file->getError() || !is_uploaded_file($file->getPathName())) {
                throw new \RuntimeException('上传文件移动失败!');
            }

            if (!$inStream = @fopen($file->getPathName(), "rb")) {
                throw new \RuntimeException('输入流打开失败!');
            }
            
        } else {    
            if (!$inStream = @fopen("php://input", "rb")) {
                throw new \RuntimeException('输入流打开失败!');
            }
        }

        while ($buff = fread($inStream, 4096)) {
            fwrite($outStream, $buff);
        }
        
        @fclose($outStream);
        @fclose($inStream);

        if (!$chunks || $chunk == $chunks - 1) {
            rename("{$filePath}.part", $filePath);
        }

        return true;
    }

    private function removeOldTmpFiles($filePath, $tmpDirectory)
    {
        if (!is_dir($tmpDirectory) || !$dir = opendir($tmpDirectory)) {
            throw new \RuntimeException('临时文件夹打开失败!');
        }

        while (($file = readdir($dir)) !== false) {
            $tmpfilePath = $tmpDirectory . DIRECTORY_SEPARATOR . $file;

            if ($tmpfilePath == "{$filePath}.part") {
                continue;
            }

            if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - 5*3600)) {
                @unlink($tmpfilePath);
            }

        }
        closedir($dir);
        return true;
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    private function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }

    private function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

}