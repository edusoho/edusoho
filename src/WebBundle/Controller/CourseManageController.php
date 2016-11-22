<?php

namespace WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class CourseManageController extends BaseController
{
    public function createAction(Request $request, $courseSetId)
    {
        if ($request->isMethod('POST')) {
            $data   = $request->request->all();
            $course = $this->getCourseService()->createCourse($data);

            return $this->listAction($request, $courseSetId);
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        return $this->render('WebBundle:CourseSetManage:course-create-modal.html.twig', array(
            'courseSet' => $courseSet
        ));
    }

    public function copyAction(Request $request, $courseSetId, $courseId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course    = $this->getCourseService()->getCourse($courseId);
        return $this->render('WebBundle:CourseSetManage:course-create-modal.html.twig', array(
            'courseSet' => $courseSet,
            'course'    => $course
        ));
    }

    public function listAction(Request $request, $courseSetId)
    {
        $courseSet     = $this->getCourseSetService()->getCourseSet($courseSetId);
        $courses       = $this->getCourseService()->findCoursesByCourseSetId($courseSetId);
        $defaultCourse = $this->getCourseService()->getDefaultCourseByCourseSetId($courseSetId);
        return $this->render('WebBundle:CourseSetManage:courses.html.twig', array(
            'courseSet'     => $courseSet,
            'courses'       => $courses,
            'defaultCourse' => $defaultCourse
        ));
    }

    public function tasksAction(Request $request, $courseSetId, $courseId)
    {
        // $course      = $this->tryManageCourse($courseId);
        $courseSet     = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course        = $this->getCourseService()->getCourse($courseId);
        $tasks         = $this->getTaskService()->findUserTasksByCourseId($courseId, $this->getUser()->getId());
        $defaultCourse = $this->getCourseService()->getDefaultCourseByCourseSetId($courseSetId);
        return $this->render('WebBundle:TaskManage:list.html.twig', array(
            'tasks'         => $tasks,
            'course'        => $course,
            'courseSet'     => $courseSet,
            'defaultCourse' => $defaultCourse
        ));
    }

    public function infoAction(Request $request, $courseSetId, $courseId)
    {
        $courseSet     = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course        = $this->getCourseService()->getCourse($courseId);
        $defaultCourse = $this->getCourseService()->getDefaultCourseByCourseSetId($courseSetId);
        return $this->render('WebBundle:CourseSetManage:course-info.html.twig', array(
            'courseSet'     => $courseSet,
            'course'        => $course,
            'defaultCourse' => $defaultCourse
        ));
    }

    public function previewAction(Request $request, $courseSetId, $courseId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course    = $this->getCourseService()->getCourse($courseId);
        return $this->render('WebBundle:CourseSet:preview.html.twig', array(
            'courseSet' => $courseSet,
            'course'    => $course
        ));
    }

    public function marketingAction(Request $request, $courseSetId, $courseId)
    {
        $courseSet     = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course        = $this->getCourseService()->getCourse($courseId);
        $defaultCourse = $this->getCourseService()->getDefaultCourseByCourseSetId($courseSetId);
        return $this->render('WebBundle:CourseSetManage:course-marketing.html.twig', array(
            'courseSet'     => $courseSet,
            'course'        => $course,
            'defaultCourse' => $defaultCourse
        ));
    }

    public function teachersAction(Request $request, $courseSetId, $courseId)
    {
        $courseSet     = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course        = $this->getCourseService()->getCourse($courseId);
        $defaultCourse = $this->getCourseService()->getDefaultCourseByCourseSetId($courseSetId);
        return $this->render('WebBundle:CourseSetManage:course-teachers.html.twig', array(
            'courseSet'     => $courseSet,
            'course'        => $course,
            'defaultCourse' => $defaultCourse
        ));
    }

    public function studentsAction(Request $request, $courseSetId, $courseId)
    {
        $courseSet     = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course        = $this->getCourseService()->getCourse($courseId);
        $defaultCourse = $this->getCourseService()->getDefaultCourseByCourseSetId($courseSetId);
        return $this->render('WebBundle:CourseSetManage:course-students.html.twig', array(
            'courseSet'     => $courseSet,
            'course'        => $course,
            'defaultCourse' => $defaultCourse
        ));
    }

    public function closeAction(Request $request, $courseSetId, $courseId)
    {
        try {
            $this->getCourseService()->closeCourse($courseId);
            return $this->createJsonResponse(array('success' => true));
        } catch (\Exception $e) {
            return $this->createJsonResponse(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    public function deleteAction(Request $request, $courseSetId, $courseId)
    {
        try {
            $this->getCourseService()->deleteCourse($courseId);
            return $this->createJsonResponse(array('success' => true));
        } catch (\Exception $e) {
            return $this->createJsonResponse(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    public function preparePublishmentAction(Request $request, $courseSetId, $courseId)
    {
        try {
            $this->getCourseService()->preparePublishment($courseId, $this->getUser()->getId());
            return $this->createJsonResponse(array('success' => true));
        } catch (\Exception $e) {
            return $this->createJsonResponse(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    public function auditPublishmentAction(Request $request, $courseSetId, $courseId)
    {
        try {
            $this->getCourseService()->auditPublishment($courseId, $this->getUser()->getId());
            return $this->createJsonResponse(array('success' => true));
        } catch (\Exception $e) {
            return $this->createJsonResponse(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }
}
