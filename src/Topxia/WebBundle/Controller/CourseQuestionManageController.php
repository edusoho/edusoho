<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class CourseQuestionManageController extends BaseController
{

    public function indexAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        
        $conditions    = $request->query->all();

        $questionCount = $this->getQuestionService()->searchQuestionsCount($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $questionCount,
            10
        );

        $questions = $this->getQuestionService()->searchQuestions(
            $conditions,
            array('createdTime' ,'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($questions, 'userId'));

        $targets = $this->get('topxia.target_helper')->getTargets(ArrayToolkit::column($questions, 'target'));

        return $this->render('TopxiaWebBundle:CourseQuestionManage:index.html.twig', array(
            'course' => $course,
            'questions' => $questions,
            'users' => $users,
            'targets' => $targets,
            'paginator' => $paginator,
            'parentId' => 0,
        ));
    }

    public function createAction(Request $request, $courseId, $type)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $question = array(
            'id' => 0,
            'type' => $type,
        );

        if ($request->getMethod() == 'POST') {
            $question = $request->request->all();
            $question = $this->getQuestionService()->createQuestion($question);

        }

        return $this->render("TopxiaWebBundle:CourseQuestionManage:question-form-{$type}.html.twig", array(
            'course' => $course,
            'question' => $question,
            'targetsChoices' => $this->getQuestionTargetChoices($course),
            // 'categoryChoices' => $this->getQuestionCategoryChoices($course),
        ));
    }

    public function updateAction(Request $request, $courseId, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $question = $this->getQuestionService()->getQuestion($id);


        if ($request->getMethod() == 'POST') {
            $question = $request->request->all();
            $question = $this->getQuestionService()->updateQuestion($id, $question);
        }

        return $this->render("TopxiaWebBundle:CourseQuestionManage:question-form-{$question['type']}.html.twig", array(
            'course' => $course,
            'question' => $question,
            'targetsChoices' => $this->getQuestionTargetChoices($course),
            // 'categoryChoices' => $this->getQuestionCategoryChoices($course),
        ));


    }

    private function getQuestionTargetChoices($course)
    {
        $lessons = $this->getCourseService()->getCourseLessons($course['id']);
        $choices = array();
        $choices["course-{$course['id']}"] = '本课程';
        foreach ($lessons as $lesson) {
            if ($lesson['type'] == 'testpaper') {
                continue;
            }
            $choices["course-{$course['id']}/lesson-{$lesson['id']}"] = "课时{$lesson['number']}：{$lesson['title']}";
        }
        return $choices;
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