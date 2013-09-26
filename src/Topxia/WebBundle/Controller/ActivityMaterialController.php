<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class ActivityMaterialController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        $activity = $this->getActivityService()->getActivity($id);
        $materials = $this->getMaterialService()->findActivityMaterials($activity['id'], 0, 100);
        return $this->render('TopxiaWebBundle:ActivityMaterialManage:material-modal.html.twig', array(
            'course' => $activity,
            'materials' => $materials,
        ));
    }

    public function downloadAction(Request $request, $courseId, $materialId)
    {
        $course = $this->getActivityService()->getActivity($courseId);
        $material = $this->getMaterialService()->getMaterial($course['id'], $materialId);
        return $this->createPrivateFileDownloadResponse($material['fileUri']);
    }

    public function deleteAction(Request $request, $id, $materialId)
    {
        $course = $this->getActivityService()->getActivity($id);
        $this->getMaterialService()->deleteMaterial($course['id'], $materialId);

        return $this->createJsonResponse(true);
    }

    public function uploadAction(Request $request, $id)
    {

        $course = $this->getActivityService()->getActivity($id);
        if (empty($course)) {
            throw $this->createNotFoundException();
        }
        if ($request->getMethod() == 'POST') {
            sleep(1);
            $fields = $request->request->all();
            $fields['file'] = $request->files->get('file');
            $fields['title'] = $fields['file']->getClientOriginalName();
            $fields['activityId'] = $course['id'];
            $material = $this->getMaterialService()->uploadMaterial($fields);

            return $this->render('TopxiaWebBundle:ActivityMaterialManage:list-item.html.twig', array(
                'material' => $material,
            ));
        }

        return $this->render('TopxiaWebBundle:ActivityMaterial:upload-modal.html.twig', array(
            'form' => $form->createView(),
            'course' => $course,
        ));

    }

	public function latestBlockAction($course)
	{
        $materials = $this->getCourseService()->findMaterials($course['id'], 0, 10);
		return $this->render('TopxiaWebBundle:CourseMaterial:latest-block.html.twig', array(
			'course' => $course,
            'materials' => $materials,
		));
	}

    private function getActivityService()
    {
        return $this->getServiceKernel()->createService('Activity.ActivityService');
    }

    private function getMaterialService()
    {
        return $this->getServiceKernel()->createService('Activity.MaterialService');
    }

    private function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }

    private function createPrivateFileDownloadResponse($fileUri)
    {
        $setting = $this->setting('file');
        $parsed = $this->getFileService()->parseFileUri($fileUri);

        $directory = $this->container->getParameter('topxia.upload.private_directory');

        $filename = $directory . '/' .  $parsed['path'];

        return BinaryFileResponse::create($filename, 200, array(), false, 'attachment');
    }
}