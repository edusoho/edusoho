<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;

class MarkerController extends BaseController
{
    public function manageAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        return $this->render('TopxiaWebBundle:Marker:index.html.twig', array(
            'course' => $course,
            'lesson' => $lesson
        ));
    }

    public function questionAction(Request $request, $courseId, $lessonId)
    {
        $course                      = $this->getCourseService()->tryManageCourse($courseId);
        $lesson                      = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        $conditions                  = $request->query->all();
        list($paginator, $questions) = $this->getPaginatorAndQuestion($request, $conditions, $course);
        return $this->render('TopxiaWebBundle:Marker:question.html.twig', array(
            'course'        => $course,
            'lesson'        => $lesson,
            'questions'     => $questions,
            'paginator'     => $paginator,
            'targetChoices' => $this->getQuestionTargetChoices($course, $lesson)
        ));
    }

    public function searchAction(Request $request, $courseId, $lessonId)
    {
        $course                      = $this->getCourseService()->tryManageCourse($courseId);
        $lesson                      = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        $conditions                  = $request->request->all();
        list($paginator, $questions) = $this->getPaginatorAndQuestion($request, $conditions, $course);
        return $this->render('TopxiaWebBundle:Marker:question-tr.html.twig', array(
            'course'    => $course,
            'paginator' => $paginator,
            'questions' => $questions
        ));
    }

    protected function getQuestionTargetChoices($course, $lesson)
    {
        $lessons                                                  = $this->getCourseService()->getCourseLessons($course['id']);
        $choices                                                  = array();
        $choices["course-{$course['id']}"]                        = '本课程';
        $choices["course-{$course['id']}/lesson-{$lesson['id']}"] = "课时{$lesson['number']}：{$lesson['title']}";
        return $choices;
    }

    protected function getPaginatorAndQuestion($request, $conditions, $course)
    {
        if (empty($conditions['target'])) {
            $conditions['targetPrefix'] = "course-{$course['id']}";
        }

        if (!empty($conditions['keyword'])) {
            $conditions['stem'] = $conditions['keyword'];
        }

        $conditions['parentId'] = 0;
        $conditions['types']    = array('determine', 'single_choice', 'uncertain_choice');
        $orderBy                = array('createdTime', 'DESC');

        $paginator = new Paginator(
            $request,
            $this->getQuestionService()->searchQuestionsCount($conditions),
            9
        );

        $questions = $this->getQuestionService()->searchQuestions(
            $conditions,
            $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return array($paginator, $questions);
    }

    protected function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
