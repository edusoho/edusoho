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
        return $this->render('WebBundle:CourseManage:create-modal.html.twig', array(
            'courseSet' => $courseSet
        ));
    }

    public function copyAction(Request $request, $courseSetId, $courseId)
    {
        $course    = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        return $this->render('WebBundle:CourseManage:create-modal.html.twig', array(
            'courseSet' => $courseSet,
            'course'    => $course
        ));
    }

    public function listAction(Request $request, $courseSetId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $courses   = $this->getCourseService()->findCoursesByCourseSetId($courseSetId);
        return $this->render('WebBundle:CourseSetManage:courses.html.twig', array(
            'courseSet' => $courseSet,
            'courses'   => $courses
        ));
    }

    public function tasksAction(Request $request, $courseSetId, $courseId)
    {
        $course      = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        $courseSet   = $this->getCourseSetService()->getCourseSet($courseSetId);
        $tasks       = $this->getTaskService()->findTasksFetchActivityByCourseId($courseId);
        $courseItems = $this->getCourseService()->getCourseItems($courseId);

        return $this->render('WebBundle:CourseManage:tasks.html.twig', array(
            'tasks'     => $tasks,
            'courseSet' => $courseSet,
            'course'    => $course,
            'items'     => $courseItems
        ));
    }

    public function infoAction(Request $request, $courseSetId, $courseId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        if ($request->isMethod('POST')) {
            $data   = $request->request->all();
            $course = $this->getCourseService()->updateCourse($courseId, $data);

            return $this->renderPage('info', $courseSet, $this->formatCourseDate($course));
        }

        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        return $this->renderPage('info', $courseSet, $this->formatCourseDate($course));
    }

    public function marketingAction(Request $request, $courseSetId, $courseId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        if ($request->isMethod('POST')) {
            $data   = $request->request->all();
            $course = $this->getCourseService()->updateCourseMarketing($courseId, $data);
            return $this->renderPage('marketing', $courseSet, $course);
        }
        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        return $this->renderPage('marketing', $courseSet, $course);
    }

    public function teachersAction(Request $request, $courseSetId, $courseId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        if ($request->isMethod('POST')) {
            $data   = $request->request->all();
            $course = $this->getCourseService()->updateCourseTeachers($courseId, $data);
            return $this->renderPage('teachers', $courseSet, $course);
        }
        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        return $this->renderPage('teachers', $courseSet, $course);
    }

    public function studentsAction(Request $request, $courseSetId, $courseId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        if ($request->isMethod('POST')) {
            $data   = $request->request->all();
            $course = $this->getCourseService()->updateCourseStudents($courseId, $data);
            return $this->renderPage('students', $courseSet, $course);
        }
        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        return $this->renderPage('students', $courseSet, $course);
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

    public function publishAction(Request $request, $courseSetId, $courseId)
    {
        try {
            $this->getCourseService()->publishCourse($courseId, $this->getUser()->getId());
            return $this->createJsonResponse(array('success' => true));
        } catch (\Exception $e) {
            return $this->createJsonResponse(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    public function courseItemsSortAction(Request $request, $courseId)
    {
        $ids = $request->request->get("ids");
        $this->getCourseService()->sortCourseItems($courseId, $ids);
        return $this->createJsonResponse(array('result' => true));
    }

    protected function renderPage($page, $courseSet, $course)
    {
        return $this->render('WebBundle:CourseManage:'.$page.'.html.twig', array(
            'courseSet' => $courseSet,
            'course'    => $course
        ));
    }

    protected function formatCourseDate($course)
    {
        if (isset($course['expiryStartDate'])) {
            $course['expiryStartDate'] = date('Y-m-d', $course['expiryStartDate']);
        }
        if (isset($course['expiryEndDate'])) {
            $course['expiryEndDate'] = date('Y-m-d', $course['expiryEndDate']);
        }

        return $course;
    }

    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
