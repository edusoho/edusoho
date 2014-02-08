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
        
        $conditions = $request->query->all();

        if (!empty($conditions['parentId'])) {

            $parentQuestion = $this->getQuestionService()->getQuestion($conditions['parentId']);
            if (empty($parentQuestion)){
                return $this->redirect($this->generateUrl('course_manage_question',array('courseId' => $courseId)));
            }

        } else {
            $conditions['parentId'] = 0;
            $parentQuestion = null;
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getQuestionService()->searchQuestionsCount($conditions),
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
            'parentQuestion' => $parentQuestion,
        ));
    }

    public function createAction(Request $request, $courseId, $type)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();

            $question = $this->getQuestionService()->createQuestion($data);

            $this->setFlashMessage('success', '题目添加成功，请继续添加！');

            if ($data['submission'] == 'continue') {
                $urlParams = ArrayToolkit::parts($question, array('target', 'difficulty', 'parentId'));
                $urlParams['type'] = $type;
                $urlParams['courseId'] = $courseId;
                $urlParams['goto'] = $request->query->get('goto', null);
                return $this->redirect($this->generateUrl('course_manage_question_create', $urlParams));
            } elseif ($data['submission'] == 'continue_sub') {
                return $this->redirect($request->query->get('goto', $this->generateUrl('course_manage_question', array('courseId' => $courseId, 'parentId' => $question['id']))));
            } else {
                return $this->redirect($request->query->get('goto', $this->generateUrl('course_manage_question', array('courseId' => $courseId))));
            }
        }


        $question = array(
            'id' => 0,
            'type' => $type,
            'target' => $request->query->get('target'),
            'difficulty' => $request->query->get('difficulty', 'normal'),
            'parentId' => $request->query->get('parentId', 0),
        );

        if ($question['parentId'] > 0) {
            $parentQuestion = $this->getQuestionService()->getQuestion($question['parentId']);
            if (empty($parentQuestion)){
                return $this->createMessageResponse('error', '父题不存在，不能创建子题！');
            }
        } else {
            $parentQuestion = null;
        }

        return $this->render("TopxiaWebBundle:CourseQuestionManage:question-form-{$type}.html.twig", array(
            'course' => $course,
            'question' => $question,
            'parentQuestion' => $parentQuestion,
            'targetsChoices' => $this->getQuestionTargetChoices($course),
            // 'categoryChoices' => $this->getQuestionCategoryChoices($course),
        ));
    }

    public function updateAction(Request $request, $courseId, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        if ($request->getMethod() == 'POST') {
            $question = $request->request->all();

            $question = $this->getQuestionService()->updateQuestion($id, $question);

            $this->setFlashMessage('success', '题目修改成功！');

            return $this->redirect($request->query->get('goto', $this->generateUrl('course_manage_question',array('courseId' => $courseId,'parentId' => $question['parentId']))));
        }

        $question = $this->getQuestionService()->getQuestion($id);
        if ($question['parentId'] > 0) {
            $parentQuestion = $this->getQuestionService()->getQuestion($question['parentId']);
        } else {
            $parentQuestion = null;
        }

        return $this->render("TopxiaWebBundle:CourseQuestionManage:question-form-{$question['type']}.html.twig", array(
            'course' => $course,
            'question' => $question,
            'parentQuestion' => $parentQuestion,
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