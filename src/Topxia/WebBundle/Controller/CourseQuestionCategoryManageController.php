<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\Service\Question\QuestionService;

class CourseQuestionCategoryManageController extends BaseController
{

    public function indexAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $categories = $this->getQuestionService()->findCategoriesByTarget("course-{$course['id']}", 0, QuestionService::MAX_CATEGORY_QUERY_COUNT);

        return $this->render('TopxiaWebBundle:CourseQuestionCategoryManage:index.html.twig', array(
            'course' => $course,
            'categories' => $categories,
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        if ($request->getMethod() == 'POST') {

            $data =$request->request->all();
            $data['target'] = "course-{$course['id']}";
            $category = $this->getQuestionService()->createCategory($data);

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
        $category = $this->getQuestionService()->getCategory($id);

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $category = $this->getQuestionService()->updateCategory($id, $data);

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
        $category = $this->getQuestionService()->getCategory($id);
        $this->getQuestionService()->deleteCategory($id);
        return $this->createJsonResponse(true);
    }

    public function sortAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        
        $this->getQuestionService()->sortCategories("course-".$course['id'], $request->request->get('ids'));
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