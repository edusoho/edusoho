<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class CourseMaterialController extends CourseBaseController
{
    public function indexAction(Request $request, $id)
    {
        list($course, $member, $response) = $this->buildLayoutDataWithTakenAccess($request, $id);

        if ($response) {
            return $response;
        }

        $conditions = array(
            'courseId'        => $id,
            'excludeLessonId' => 0,
            'source'          => 'coursematerial',
            'type'            => 'course'
        );

        $paginator = new Paginator(
            $request,
            $this->getMaterialService()->searchMaterialCount($conditions),
            20
        );

        $materials = $this->getMaterialService()->searchMaterials(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $lessons = $this->getCourseService()->getCourseLessons($course['id']);
        $lessons = ArrayToolkit::index($lessons, 'id');

        return $this->render("TopxiaWebBundle:CourseMaterial:index.html.twig", array(
            'course'    => $course,
            'member'    => $member,
            'lessons'   => $lessons,
            'materials' => $materials,
            'paginator' => $paginator
        ));
    }

    public function downloadAction(Request $request, $courseId, $materialId)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);

        if ($member && !$this->getCourseService()->isMemberNonExpired($course, $member)) {
            return $this->redirect($this->generateUrl('course_materials', array('id' => $courseId)));
        }

        if ($member && $member['levelId'] > 0) {
            if ($this->getVipService()->checkUserInMemberLevel($member['userId'], $course['vipLevelId']) != 'ok') {
                return $this->redirect($this->generateUrl('course_show', array('id' => $id)));
            }
        }

        $material = $this->getMaterialService()->getMaterial($courseId, $materialId);

        if (empty($material)) {
            throw $this->createNotFoundException();
        }

        return $this->forward('TopxiaWebBundle:UploadFile:download', array('fileId' => $material['fileId']));
    }

    public function deleteAction(Request $request, $id, $materialId)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $this->getCourseService()->deleteCourseMaterial($id, $materialId);
        return $this->createJsonResponse(true);
    }

    protected function getMaterialService()
    {
        return $this->getServiceKernel()->createService('Course.MaterialService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    protected function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    }
}
