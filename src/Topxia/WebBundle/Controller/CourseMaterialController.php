<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class CourseMaterialController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        $course = $this->getCourseService()->getCourse($id);

        $paginator = new Paginator(
            $request,
            $this->getMaterialService()->getMaterialCount($id),
            20
        );

        $materials = $this->getMaterialService()->findCourseMaterials(
            $id,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render("TopxiaWebBundle:CourseMaterial:index.html.twig", array(
            'course' => $course,
            'materials' => $materials,
            'paginator' => $paginator,
        ));
    }

    public function downloadAction(Request $request, $courseId, $materialId)
    {
        $course = $this->getCourseService()->tryTakeCourse($courseId);
        $material = $this->getMaterialService()->getMaterial($courseId, $materialId);
        return $this->createPrivateFileDownloadResponse($material['fileUri']);
    }

    public function deleteAction(Request $request, $id, $materialId)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $this->getCourseService()->deleteCourseMaterial($id, $materialId);
        return $this->createJsonResponse(true);
    }

	public function latestBlockAction($course)
	{
        $materials = $this->getCourseService()->findMaterials($course['id'], 0, 10);
		return $this->render('TopxiaWebBundle:CourseMaterial:latest-block.html.twig', array(
			'course' => $course,
            'materials' => $materials,
		));
	}

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getMaterialService()
    {
        return $this->getServiceKernel()->createService('Course.MaterialService');
    }

    private function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }

    private function createPrivateFileDownloadResponse($fileUri)
    {
        $setting = $this->setting('file');
        $parsed = $this->getFileService()->parseFileUri($fileUri);

        $directory = dirname($this->get('kernel')->getRootDir()). '/' . $setting[$parsed['access'].'_directory'];

        $filename = $directory . '/' .  $parsed['path'];


        $response = BinaryFileResponse::create($filename, 200, array(), false, 'attachment');

        // $response = new Response();

        // $response->headers->set('Cache-Control', 'private');
        // $response->headers->set('Content-type', mime_content_type($filename));
        // $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($filename) . '"');
        // $response->headers->set('Content-length', filesize($filename));

        // $response->sendHeaders();

        // $response->setContent(readfile($filename));

        return $response;
    }
}