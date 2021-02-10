<?php

namespace AppBundle\Controller\Course;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Biz\Course\MaterialException;
use Biz\Course\MemberException;
use Symfony\Component\HttpFoundation\Request;
use VipPlugin\Biz\Marketing\Service\VipRightService;
use VipPlugin\Biz\Marketing\VipRightSupplier\ClassroomVipRightSupplier;
use VipPlugin\Biz\Marketing\VipRightSupplier\CourseVipRightSupplier;

class MaterialController extends CourseBaseController
{
    public function indexAction(Request $request, $course, $member = [])
    {
        $courseMember = $this->getCourseMember($request, $course);
        if (empty($courseMember)) {
            $this->createNewException(MemberException::NOTFOUND_MEMBER());
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        $conditions = [
            'courseId' => $course['id'],
            'excludeLessonId' => 0,
            'source' => 'coursematerial',
            'type' => 'course',
        ];

        $paginator = new Paginator(
            $request,
            $this->getMaterialService()->countMaterials($conditions),
            20
        );

        $materials = $this->getMaterialService()->searchMaterials(
            $conditions,
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $tasks = $this->getTaskService()->searchTasks(['courseId' => $course['id'], 'type' => 'download'], [], 0, 100);
        $tasks = ArrayToolkit::index($tasks, 'activityId');

        return $this->render('course/tabs/material.html.twig', [
            'courseSet' => $courseSet,
            'course' => $course,
            'member' => $member,
            'tasks' => $tasks,
            'materials' => $materials,
            'paginator' => $paginator,
        ]);
    }

    public function downloadAction(Request $request, $courseId, $materialId)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);

        if ($member && !$this->getMemberService()->isMemberNonExpired($course, $member)) {
            return $this->redirect($this->generateUrl('my_course_show', ['id' => $courseId, 'tab' => 'material']));
        }

        if ($member && 'vip_join' == $member['joinedChannel']) {
            if (empty($this->getVipRightService()->getVipRightBySupplierCodeAndUniqueCode(CourseVipRightSupplier::CODE, $course['id']))) {
                return $this->redirect($this->generateUrl('course_show', ['id' => $course['id']]));
            } elseif (empty($course['parentId'])
                && $this->isVipPluginEnabled()
                && 'ok' != $this->getVipService()->checkUserVipRight($member['userId'], CourseVipRightSupplier::CODE, $course['id'])
            ) {
                return $this->redirect($this->generateUrl('course_show', ['id' => $course['id']]));
            } elseif (!empty($course['parentId'])) {
                $classroom = $this->getClassroomService()->getClassroomByCourseId($course['id']);
                if (!empty($classroom)
                    && $this->isVipPluginEnabled()
                    && 'ok' != $this->getVipService()->checkUserVipRight($member['userId'], ClassroomVipRightSupplier::CODE, $classroom['id'])
                ) {
                    return $this->redirect($this->generateUrl('course_show', ['id' => $course['id']]));
                }
            }
        }

        $material = $this->getMaterialService()->getMaterial($courseId, $materialId);

        if (empty($material)) {
            $this->createNewException(MaterialException::NOTFOUND_MATERIAL());
        }

        if ('courseactivity' == $material['source'] || !$material['lessonId']) {
            return $this->createMessageResponse('error', '无权下载该资料');
        }

        return $this->forward('AppBundle:UploadFile:download', ['fileId' => $material['fileId']]);
    }

    public function deleteAction(Request $request, $id, $materialId)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $this->getMaterialService()->deleteMaterial($id, $materialId);

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
        return $this->createService('VipPlugin:Vip:VipService');
    }

    /**
     * @return VipRightService
     */
    private function getVipRightService()
    {
        return $this->createService('VipPlugin:Marketing:VipService');
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
