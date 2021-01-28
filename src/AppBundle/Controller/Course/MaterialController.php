<?php

namespace AppBundle\Controller\Course;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\MaterialException;
use Biz\Course\MemberException;
use Symfony\Component\HttpFoundation\Request;

class MaterialController extends CourseBaseController
{
    public function indexAction(Request $request, $course, $member = array())
    {
        $courseMember = $this->getCourseMember($request, $course);
        if (empty($courseMember)) {
            $this->createNewException(MemberException::NOTFOUND_MEMBER());
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        $conditions = array(
            'courseId' => $course['id'],
            'excludeLessonId' => 0,
            'source' => 'coursematerial',
            'type' => 'course',
        );

        $paginator = new Paginator(
            $request,
            $this->getMaterialService()->countMaterials($conditions),
            20
        );

        $materials = $this->getMaterialService()->searchMaterials(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $tasks = $this->getTaskService()->searchTasks(array('courseId' => $course['id'], 'type' => 'download'), array(), 0, 100);
        $tasks = ArrayToolkit::index($tasks, 'activityId');

        return $this->render('course/tabs/material.html.twig', array(
            'courseSet' => $courseSet,
            'course' => $course,
            'member' => $member,
            'tasks' => $tasks,
            'materials' => $materials,
            'paginator' => $paginator,
        ));
    }

    public function downloadAction(Request $request, $courseId, $materialId)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);

        if ($member && !$this->getMemberService()->isMemberNonExpired($course, $member)) {
            return $this->redirect($this->generateUrl('my_course_show', array('id' => $courseId, 'tab' => 'material')));
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
            $this->createNewException(MaterialException::NOTFOUND_MATERIAL());
        }

        if ($material['source'] == 'courseactivity' || !$material['lessonId']) {
            return $this->createMessageResponse('error', '无权下载该资料');
        }

        return $this->forward('AppBundle:UploadFile:download', array('fileId' => $material['fileId']));
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
