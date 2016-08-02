<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\FileToolkit;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class CourseMaterialManageController extends BaseController
{
    public function indexAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);

        $materials = $this->getMaterialService()->searchMaterials(
            array('lessonId' => $lesson['id'], 'source' => 'coursematerial', 'type' => 'course'),
            array('createdTime', 'DESC'), 0, 100
        );
        return $this->render('TopxiaWebBundle:CourseMaterialManage:material-modal.html.twig', array(
            'course'         => $course,
            'lesson'         => $lesson,
            'materials'      => $materials,
            'storageSetting' => $this->setting('storage'),
            'targetType'     => 'coursematerial',
            'targetId'       => $course['id']
        ));
    }

    public function uploadAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();

            if (empty($fields['fileId']) && empty($fields['link'])) {
                throw $this->createNotFoundException();
            }

            $fields['courseId'] = $course['id'];
            $fields['lessonId'] = $lessonId;
            $fields['type']     = 'course';
            $fields['source']   = 'coursematerial';

            $material = $this->getMaterialService()->uploadMaterial($fields);

            return $this->render('TopxiaWebBundle:CourseMaterialManage:list-item.html.twig', array(
                'material' => $material,
                'course'   => $course
            ));
        }

        return $this->render('TopxiaWebBundle:CourseMaterial:upload-modal.html.twig', array(
            'form'   => $form->createView(),
            'course' => $course
        ));
    }

    public function deleteAction(Request $request, $courseId, $lessonId, $materialId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $material = $this->getMaterialService()->getMaterial($courseId, $materialId);
        if ($material) {
            $this->getMaterialService()->updateMaterial($materialId, array('lessonId' => 0), array('lessonId' => $lessonId, 'materialId' => $materialId, 'fileId' => $material['fileId']));
        }

        return $this->createJsonResponse(true);
    }

    public function browserAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        return $this->forward('TopxiaWebBundle:CourseMaterialManage:materialBrowser', array(
            'request'  => $request,
            'courseId' => $courseId
        ));
    }

    public function materialBrowserAction(Request $request, $courseId)
    {
        $conditions = array();
        $type       = $request->query->get('type');
        if (!empty($type)) {
            $conditions['type'] = $type;
        }

        $courseType = $request->query->get('courseType');
        $courseType = empty($courseType) ? 'course' : $courseType;

        $courseMaterials = $this->getMaterialService()->searchMaterialsGroupByFileId(
            array(
                'courseId' => $courseId,
                'type'     => $courseType
            ),
            array('createdTime', 'DESC'),
            0,
            PHP_INT_MAX
        );

        $conditions['ids'] = $courseMaterials ? ArrayToolkit::column($courseMaterials, 'fileId') : array(-1);
        $paginator         = new Paginator(
            $request,
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

    public function createAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        return $this->render('TopxiaWebBundle:CourseMaterialManage:material-create-modal.html.twig', array(
            'course'         => $course,
            'storageSetting' => $this->setting('storage'),
            'targetType'     => 'coursematerial',
            'targetId'       => $course['id']
        ));
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

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getMaterialService()
    {
        return $this->getServiceKernel()->createService('Course.MaterialService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }
}
