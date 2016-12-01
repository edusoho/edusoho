<?php
namespace WebBundle\Controller;

use Biz\Task\Service\TaskService;
use Biz\Task\Strategy\StrategyContext;
use Symfony\Component\HttpFoundation\Request;

class CourseManageController extends BaseController
{
    public function createAction(Request $request, $courseSetId)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->getCourseService()->createCourse($data);

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
        $course          = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        $courseSet       = $this->getCourseSetService()->getCourseSet($courseSetId);
        $tasks           = $this->getTaskService()->findTasksFetchActivityByCourseId($courseId);
        $courseItems     = $this->getCourseService()->findCourseItems($courseId);
        $tasksRenderPage = $this->createCourseStrategy($course)->getTasksRenderPage();

        return $this->render($tasksRenderPage, array(
            'tasks'     => $tasks,
            'courseSet' => $courseSet,
            'course'    => $course,
            'items'     => $courseItems
        ));
    }

    protected function createCourseStrategy($course)
    {
        return StrategyContext::getInstance()->createStrategy($course['isDefault'], $this->get('biz'));
    }

    public function infoAction(Request $request, $courseSetId, $courseId)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->getCourseService()->updateCourse($courseId, $data);

            return $this->redirect($this->generateUrl('course_set_manage_course_info', array('courseSetId' => $courseSetId, 'courseId' => $courseId)));
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course    = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        return $this->render('WebBundle:CourseManage:info.html.twig', array(
            'courseSet' => $courseSet,
            'course'    => $this->formatCourseDate($course)
        ));
    }

    public function marketingAction(Request $request, $courseSetId, $courseId)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->getCourseService()->updateCourseMarketing($courseId, $data);

            return $this->redirect($this->generateUrl('course_set_manage_course_marketing', array('courseSetId' => $courseSetId, 'courseId' => $courseId)));
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course    = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        return $this->render('WebBundle:CourseManage:marketing.html.twig', array(
            'courseSet' => $courseSet,
            'course'    => $course
        ));
    }

    public function teachersAction(Request $request, $courseSetId, $courseId)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->getCourseService()->updateCourseTeachers($courseId, $data);

            return $this->redirect($this->generateUrl('course_set_manage_course_teachers', array('courseSetId' => $courseSetId, 'courseId' => $courseId)));
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course    = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        return $this->render('WebBundle:CourseManage:teachers.html.twig', array(
            'courseSet' => $courseSet,
            'course'    => $course
        ));
    }

    public function studentsAction(Request $request, $courseSetId, $courseId)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->getCourseService()->updateCourseStudents($courseId, $data);

            return $this->redirect($this->generateUrl('course_set_manage_course_students', array('courseSetId' => $courseSetId, 'courseId' => $courseId)));
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course    = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        return $this->render('WebBundle:CourseManage:students.html.twig', array(
            'courseSet' => $courseSet,
            'course'    => $course
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

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
