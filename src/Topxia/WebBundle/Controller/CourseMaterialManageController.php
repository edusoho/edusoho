<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class CourseMaterialManageController extends BaseController
{

	public function indexAction(Request $request, $courseId, $lessonId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
		$materials = $this->getMaterialService()->findLessonMaterials($lesson['id'], 0, 100);
		return $this->render('TopxiaWebBundle:CourseMaterialManage:material-modal.html.twig', array(
			'course' => $course,
			'lesson' => $lesson,
			'materials' => $materials,
		));
	}

	public function uploadAction(Request $request, $courseId, $lessonId)
	{

        $course = $this->getCourseService()->tryManageCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        if (empty($lesson)) {
        	throw $this->createNotFoundException();
        }

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $fields['file'] = $request->files->get('file');
            $fields['title'] = $fields['file']->getClientOriginalName();
            $fields['courseId'] = $course['id'];
            $fields['lessonId'] = $lesson['id'];
            
            $material = $this->getMaterialService()->uploadMaterial($fields);

			return $this->render('TopxiaWebBundle:CourseMaterialManage:list-item.html.twig', array(
				'material' => $material,
			));
        }

		return $this->render('TopxiaWebBundle:CourseMaterial:upload-modal.html.twig', array(
			'form' => $form->createView(),
			'course' => $course,
		));

	}

	public function deleteAction(Request $request, $courseId, $lessonId, $materialId)
	{
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $this->getMaterialService()->deleteMaterial($courseId, $materialId);
        return $this->createJsonResponse(true);
	}

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getMaterialService()
    {
        return $this->getServiceKernel()->createService('Course.MaterialService');
    }
}