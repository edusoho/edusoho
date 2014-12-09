<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Service\Course\CourseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\WebBundle\Controller\BaseController;

class SubCourseManageController extends BaseController
{
    public function listAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);
        list($relations, $subCourses) = $this->getCourseService()->findSubcoursesByCourseId($id);
        $teachers = $this->findTeachers($subCourses);
        return $this->render('CustomWebBundle:SubCourseManage:list.html.twig', array(
            'course' => $course,
            'relations' => $relations,
            'subCourses' => $subCourses,
            'teachers' => $teachers,
        ));
    }

    public function addAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);
        if ($request->getMethod() == 'POST') {
            $subcourseId = $request->query->get('subcourseId');
            $this->getCourseService()->addSubcourse(array('courseId' => $id, 'subcourseId' => $subcourseId));
            return $this->createJsonResponse(true);
        }
        $query = $request->query->all();
        $subjects = $this->getCategoryService()->findCategoriesByIds($course['subjectIds']);
        $newSubjects = array();
        foreach ($subjects as $key => $subject) {
            $newSubjects[$subject['id']] = $subject['name'];
        }
        list($relations, $subCourses) = $this->getCourseService()->findSubcoursesByCourseId($id);
        $excludeIds = ArrayToolkit::column($relations, 'subcourseId');
        $conditions = $request->query->all();
        $conditions['status'] = 'published';
        $conditions['type'] = 'not-package';
        $conditions['excludeIds'] = $excludeIds;
        if(!isset($conditions['subjectId'])) {
            $conditions['subjectIds'] = ArrayToolkit::column($subjects, 'id');
        }
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchCourseCount($conditions)
            , 5
        );
        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $teachers = $this->findTeachers($courses);
        return $this->render('CustomWebBundle:SubCourseManage:add-subcourse-modal.html.twig', array(
            'coursePackage' => $course,
            'courses' => $courses,
            'subjects' => $newSubjects,
            'teachers' => $teachers,
            'paginator' => $paginator,
        ));
    }

    public function deleteAction(Request $request, $courseId, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $this->getCourseService()->deleteSubCourse($id);
        return $this->createJsonResponse(true);
    }

    public function sortAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $sortIds = $request->request->get('ids');
        $this->getCourseService()->sortSubcoursesByIds($sortIds);
        return $this->createJsonResponse(true);
    }

    private function findTeachers($courses)
    {
        $teacherIds =array();
        foreach ($courses as $key => $course) {
            foreach ($course['teacherIds'] as $teacherId) {
                if(!in_array($teacherId, $teacherIds)) {
                    $teacherIds[] = $teacherId;
                }
            }
        }
        $teachers = $this->getUserService()->findUsersByIds($teacherIds);
        return $teachers;
    }
    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }
}
