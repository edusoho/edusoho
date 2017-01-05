<?php
namespace AppBundle\Controller\Course;

use AppBundle\Controller\CourseBaseController;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class CourseMaterialController extends CourseBaseController
{
    public function indexAction(Request $request, $id)
    {
        list($courseSet, $course, $member, $response) = $this->tryBuildCourseLayoutData($request, $id);

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
            array('createdTime'=> 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $tasks = $this->getTaskService()->search(array('courseId' => $id, 'type' => 'download'), array(), 0, 100);
        $tasks = ArrayToolkit::index($tasks, 'activityId');

        return $this->render("course/material/list.html.twig", array(
            'courseSet' => $courseSet,
            'course'    => $course,
            'member'    => $member,
            'tasks'     => $tasks,
            'materials' => $materials,
            'paginator' => $paginator
        ));
    }

    public function downloadAction(Request $request, $courseId, $materialId)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);

        if ($member && !$this->getCourseMemberService()->isMemberNonExpired($course, $member)) {
            return $this->redirect($this->generateUrl('course_materials', array('id' => $courseId)));
        }

        if ($member && $member['levelId'] > 0) {
            if (empty($course['vipLevelId'])) {
                return $this->redirect($this->generateUrl('course_show', array('id' => $course['id'])));
            } elseif (empty($course['parentId'])
                && $this->isVipPluginEnabled()
                && $this->getVipService()->checkUserInMemberLevel($member['userId'], $course['vipLevelId']) != 'ok'
            ) {
                return $this->redirect($this->generateUrl('course_show', array('id' => $course['id'])));
            } elseif (!empty($course['parentId'])) {
                $classroom = $this->getClassroomService()->getClassroomByCourseId($course['id']);
                if (!empty($classroom)
                    && $this->isVipPluginEnabled()
                    && $this->getVipService()->checkUserInMemberLevel($member['userId'], $classroom['vipLevelId']) != 'ok'
                ) {
                    return $this->redirect($this->generateUrl('course_show', array('id' => $course['id'])));
                }
            }
        }

        $material = $this->getMaterialService()->getMaterial($courseId, $materialId);

        if (empty($material)) {
            throw $this->createNotFoundException();
        }

        if ($material['source'] == 'courselesson' || !$material['lessonId']) {
            return $this->createMessageResponse('error', $this->trans('无权下载该资料'));
        }

        return $this->forward('AppBundle:UploadFile:download', array('fileId' => $material['fileId']));
    }

    public function deleteAction(Request $request, $id, $materialId)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $this->getCourseService()->deleteCourseMaterial($id, $materialId);
        return $this->createJsonResponse(true);
    }

    protected function isVipPluginEnabled()
    {
        return $this->isPluginInstalled('Vip') && $this->setting('vip.enabled');
    }

    protected function getMaterialService()
    {
        return $this->createService('Course:MaterialService');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    protected function getVipService()
    {
        return $this->createService('Vip:Vip.VipService');
    }

    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }
}
