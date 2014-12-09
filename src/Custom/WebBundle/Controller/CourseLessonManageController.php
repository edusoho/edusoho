<?php 

namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class CourseLessonManageController extends BaseController
{
    public function createAction(Request $request,$id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $categoryId = $course['subjectIds'];
        //test
        $categoryId = 4;
        $category = $this->getCategoryService()->getCategory($categoryId);

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

    public function FunctionName($value='')
    {
        # code...
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