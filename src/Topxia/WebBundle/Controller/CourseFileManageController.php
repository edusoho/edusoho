<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class CourseFileManageController extends BaseController
{

    public function indexAction(Request $request, $id)
    {
    	$paginator = new Paginator($request, 1);
        $course = $this->getCourseService()->tryManageCourse($id);
        $user = $this->getCurrentUser();
        $courseWares = array(
            array(
                'id'=>1,
                'fileName'=>'从入门到精通',
                'fileType'=>'audio',
                'fileSize'=>1000,
                'updateTime'=>1383190130,
                'updateUser'=>$user,
                'createdTime'=>1383190130,
                'createdUser'=>$user
                ),
            array(
                'id'=>2,
                'fileName'=>'从入门到精通',
                'fileType'=>'audio',
                'fileSize'=>1000,
                'updateTime'=>1383190130,
                'updateUser'=>$user,
                'createdTime'=>1383190130,
                'createdUser'=>$user
                ));

        return $this->render('TopxiaWebBundle:CourseFileManage:index.html.twig', array(
            'course' => $course,
            'courseWares' => $courseWares,
            'paginator' => $paginator
        ));
    }

    public function materialAction(Request $request, $id)
    {
        $paginator = new Paginator($request, 1);
        $course = $this->getCourseService()->tryManageCourse($id);
        $user = $this->getCurrentUser();
        $courseMaterials = array(
            array(
                'id'=>1,
                'fileName'=>'从入门到精通',
                'fileType'=>'audio',
                'fileSize'=>1000,
                'updateTime'=>1383190130,
                'updateUser'=>$user,
                'createdTime'=>1383190130,
                'createdUser'=>$user
                ),
            array(
                'id'=>2,
                'fileName'=>'从入门到精通',
                'fileType'=>'audio',
                'fileSize'=>1000,
                'updateTime'=>1383190130,
                'updateUser'=>$user,
                'createdTime'=>1383190130,
                'createdUser'=>$user
        ));

        return $this->render('TopxiaWebBundle:CourseFileManage:materials.html.twig', array(
            'course' => $course,
            'courseMaterials' => $courseMaterials,
            'paginator' => $paginator
        ));
    }

    public function uploadCourseWareAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);
        
        return $this->render('TopxiaWebBundle:CourseFileManage:modal-upload-course-ware.html.twig', array(
            'course' => $course
        ));
    }

    public function submitUploadCourseWaresAction(Request $request, $id)
    {
        return $this->redirect($this->generateUrl('course_manage_files',array('id'=>$id)));
    }

    // 这个会根据JS里面设定的chunk大小分次上传
    public function uploadCourseWareAsChunkAction(Request $request, $id)
    {

        // flash 只会上传一次token
        $fileName = null;
        $uploadedChunk = $request->request->all();
        $file = $request->files->get('file');
        $fileName = $file->getFilename();
        if ($uploadedChunk) {
            $fileName = $uploadedChunk["name"];
        } else {
            $fileName = uniqid("file_");
        }

        $tmpPluploadDirectory = $this->container->getParameter('topxia.upload.public_directory').DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'plupload';
        $cleanupTargetDir = true;

        if (!file_exists($tmpPluploadDirectory)) {
            @mkdir($tmpPluploadDirectory);
        }

        $filePath = $tmpPluploadDirectory . DIRECTORY_SEPARATOR . $fileName;
        $chunk = isset($uploadedChunk["chunk"]) ? intval($uploadedChunk["chunk"]) : 0;
        $chunks = isset($uploadedChunk["chunks"]) ? intval($uploadedChunk["chunks"]) : 0;

        $this->removeOldTmpFiles($cleanupTargetDir, $filePath, $tmpPluploadDirectory);
        $this->openTmpFiles($chunks, $chunk, $filePath, $file);
        
        // 写入数据库
        if (!$chunks || $chunk == $chunks - 1) {
            $result = $this->getFileService()->uploadFile('course_private', new File($filePath));
            return $this->createJsonResponse(array('status' => 'ok', 'file' => $result));
        } else {
            return $this->createJsonResponse(array('status' => 'uploading', 'message' => '正在上传...'));
        }

    }

    private function openTmpFiles($chunks, $chunk, $filePath, $file)
    {
        if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }

        if (!empty($file)) {
            if ($file->getError() || !is_uploaded_file($file->getPathName())) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
            }

            if (!$in = @fopen($file->getPathName(), "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
            
        } else {    
            if (!$in = @fopen("php://input", "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        }

        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }
        
        @fclose($out);
        @fclose($in);

        if (!$chunks || $chunk == $chunks - 1) {
            rename("{$filePath}.part", $filePath);
        }

    }

    private function removeOldTmpFiles($cleanupTargetDir, $filePath, $tmpPluploadDirectory)
    {
        if ($cleanupTargetDir) {
            if (!is_dir($tmpPluploadDirectory) || !$dir = opendir($tmpPluploadDirectory)) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
            }

            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $tmpPluploadDirectory . DIRECTORY_SEPARATOR . $file;

                if ($tmpfilePath == "{$filePath}.part") {
                    continue;
                }

                if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - 5 * 3600)) {
                    @unlink($tmpfilePath);
                }
            }
            closedir($dir);
        }   
    }
    
    public function uploadCourseMaterialAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        return $this->render('TopxiaWebBundle:CourseFileManage:modal-upload-course-material.html.twig', array(
            'course' => $course
        ));
    }

    public function deleteCourseFilesAction(Request $request, $id, $type)
    {
        $ids = $request->request->get('ids', array());

        $course = $this->getCourseService()->tryManageCourse($id);

        return $this->createJsonResponse(true);
    }

    public function renameCourseFilesAction(Request $request, $id, $type)
    {
        $ids = $request->request->get('ids', array());
        $user = $this->getCurrentUser();
    	$course = $this->getCourseService()->tryManageCourse($id);
        
        $courseMaterials = array(
            array(
                'id'=>1,
                'fileName'=>'从入门到精通',
                'fileType'=>'audio',
                'fileSize'=>1000,
                'updateTime'=>1383190130,
                'updateUser'=>$user,
                'createdTime'=>1383190130,
                'createdUser'=>$user
                ),
            array(
                'id'=>2,
                'fileName'=>'从入门到精通',
                'fileType'=>'audio',
                'fileSize'=>1000,
                'updateTime'=>1383190130,
                'updateUser'=>$user,
                'createdTime'=>1383190130,
                'createdUser'=>$user
        ));

        $html = $this->renderView('TopxiaWebBundle:CourseFileManage:modal-rename-course-files.html.twig', array(
            'course' => $course,
            'courseFiles' => $courseMaterials));
        return $this->createJsonResponse(array('status' => 'ok', 'html' => $html));
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

}