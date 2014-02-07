<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class CourseQuestionCategoryManageController extends BaseController
{
    const MAX_CATEGORY_COUNT = 1000;

    public function indexAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $categories = $this->getQuestionService()->findCategoriesByTarget("course-{$course['id']}", 0, self::MAX_CATEGORY_COUNT);

        return $this->render('TopxiaWebBundle:CourseQuestionCategoryManage:index.html.twig', array(
            'course' => $course,
            'categories' => $categories,
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        if ($request->getMethod() == 'POST') {

            $field =$request->request->all();
            $field['courseId'] = $courseId;
            $category = $this->getQuestionService()->createCategory($field);

            return $this->render('TopxiaWebBundle:CourseQuestionCategoryManage:tr.html.twig', array(
                'category' => $category,
                'course' => $course
            ));
        }
        return $this->render('TopxiaWebBundle:CourseQuestionCategoryManage:modal.html.twig', array(
            'course' => $course,
        ));
    }

    public function updateAction(Request $request, $courseId, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $category = $this->getQuestionService()->getCategory($categoryId);
        if ($request->getMethod() == 'POST') {
            $field = $request->request->all();

            $category = $this->getQuestionService()->updateCategory($categoryId, $field);
            return $this->render('TopxiaWebBundle:CourseQuestionCategoryManage:tr.html.twig', array(
                'category' => $category,
                'course' => $course,
            ));
        }
        return $this->render('TopxiaWebBundle:CourseQuestionCategoryManage:modal.html.twig', array(
            'category' => $category,
            'course' => $course,
        ));
    }

    public function deleteAction(Request $request, $courseId, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $category = $this->getQuestionService()->getCategory($categoryId);
        if (empty($category)) {
            throw $this->createNotFoundException();
        }
        $this->getQuestionService()->deleteCategory($categoryId);
        return $this->createJsonResponse(true);
    }

    public function sortAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $this->getQuestionService()->sortCategories($course['id'], $request->request->get('ids'));
        return $this->createJsonResponse(true);
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }


}