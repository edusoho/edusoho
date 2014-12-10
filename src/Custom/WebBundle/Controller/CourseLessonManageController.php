<?php 

namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class CourseLessonManageController extends BaseController
{
    public function createAction(Request $request,$id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $categoryId = $course['subjectIds'][0];
        $category = $this->getCategoryService()->getCategory($categoryId);

        if (empty($category)) {
            throw $this->createNotFoundException("科目(#{$categoryId})不存在，创建课时失败！");
        }

        if ($request->getMethod() == "POST") {

            $formData = $request->request->all();
            $lesson = $this->getCourseService()->createLesson($formData);

            return $this->render('TopxiaWebBundle:CourseLessonManage:list-item.html.twig', array(
                'course' => $course,
                'lesson' => $lesson,
            ));
        }

        return $this->render('CustomWebBundle:CourseLessonManage:lesson-modal.html.twig', array(
            'course' => $course,
            'category' => $category
        ));
    }

    public function editAction(Request $request,$courseId,$lessonId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $categoryId = $course['subjectIds'][0];
        $category = $this->getCategoryService()->getCategory($categoryId);

        if (empty($category)) {
            throw $this->createNotFoundException("科目(#{$categoryId})不存在，编辑课时失败！");
        }

        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);

        if (empty($lesson)) {
            throw $this->createNotFoundException("课时(#{$lessonId})不存在！");
        }

        $courseware = $this->getCoursewareService()->getCourseware($lesson['coursewareId']);

        if($request->getMethod() == 'POST'){

            $fields = $request->request->all();
            $lesson = $this->getCourseService()->updateLesson($course['id'], $lesson['id'], $fields);

            return $this->render('TopxiaWebBundle:CourseLessonManage:list-item.html.twig', array(
                'course' => $course,
                'lesson' => $lesson
            ));
        }

        return $this->render('CustomWebBundle:CourseLessonManage:lesson-modal.html.twig', array(
            'course' => $course,
            'category' => $category,
            'lesson' => $lesson,
            'courseware' => $courseware
        ));
    }

    private function getCoursewareService()
    {
        return $this->getServiceKernel()->createService('Courseware.CoursewareService');
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}