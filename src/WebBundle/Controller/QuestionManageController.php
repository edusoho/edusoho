<?php

namespace WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class QuestionManageController extends BaseController
{
    public function indexAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $conditions = $request->query->all();

        $conditions['courseId'] = $courseId;

        if (!empty($conditions['keyword'])) {
            $conditions['stem'] = $conditions['keyword'];
        }

        if (!empty($conditions['target'])) {
            $conditions['lessonId'] = $conditions['target'];
        }

        if (!empty($conditions['parentId'])) {
            $parentQuestion = $this->getQuestionService()->get($conditions['parentId']);

            if (!$parentQuestion) {
                return $this->redirect($this->generateUrl('course_manage_question', array('courseId' => $courseId)));
            }

            $orderBy = array('createdTime' => 'ASC');
        } else {
            $parentQuestion = null;
            $orderBy        = array('createdTime' => 'DESC');
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getQuestionService()->searchCount($conditions),
            10
        );

        $questions = $this->getQuestionService()->search(
            $conditions,
            $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users         = $this->getUserService()->findUsersByIds(ArrayToolkit::column($questions, 'userId'));
        $questionTypes = $this->getQuestionService()->getQuestionTypes();

        return $this->render('WebBundle:QuestionManage:index.html.twig', array(
            'course'         => $course,
            'questions'      => $questions,
            'users'          => $users,
            'paginator'      => $paginator,
            'parentQuestion' => $parentQuestion,
            'conditions'     => $conditions,
            'questionTypes'  => $questionTypes
        ));
    }

    public function createAction(Request $request, $courseId, $type)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        if ($request->getMethod() == 'POST') {
            $data       = $request->request->all();
            $attachment = $request->request->get('attachment');

            $data['courseId'] = $courseId;
            $question         = $this->getQuestionService()->create($data);

            $this->getUploadFileService()->createUseFiles($attachment['stem']['fileIds'], $question['id'], $attachment['stem']['targetType'], $attachment['stem']['type']);
            $this->getUploadFileService()->createUseFiles($attachment['analysis']['fileIds'], $question['id'], $attachment['analysis']['targetType'], $attachment['analysis']['type']);

            if ($data['submission'] == 'continue') {
                $urlParams             = ArrayToolkit::parts($question, array('target', 'difficulty', 'parentId'));
                $urlParams['type']     = $type;
                $urlParams['courseId'] = $courseId;
                $urlParams['goto']     = $request->query->get('goto', null);
                $this->setFlashMessage('success', $this->getServiceKernel()->trans('题目添加成功，请继续添加。'));
                return $this->redirect($this->generateUrl('course_manage_question_create', $urlParams));
            } elseif ($data['submission'] == 'continue_sub') {
                $this->setFlashMessage('success', $this->getServiceKernel()->trans('题目添加成功，请继续添加子题。'));
                return $this->redirect($request->query->get('goto', $this->generateUrl('course_manage_question', array('courseId' => $courseId, 'parentId' => $question['id']))));
            } else {
                $this->setFlashMessage('success', $this->getServiceKernel()->trans('题目添加成功。'));
                return $this->redirect($request->query->get('goto', $this->generateUrl('course_manage_question', array('courseId' => $courseId))));
            }
        }

        $questionConfig   = $this->getQuestionService()->getQuestionConfig($type);
        $createController = $questionConfig->getAction('create');

        return $this->forward($createController, array(
            'courseId' => $courseId,
            'type'     => $type
        ));
    }

    public function questionPickerAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $conditions = $request->query->all();

        if (empty($conditions['target'])) {
            $conditions['targetPrefix'] = "course-{$course['id']}";
        }

        $conditions['parentId'] = 0;

        if (empty($conditions['excludeIds'])) {
            unset($conditions['excludeIds']);
        } else {
            $conditions['excludeIds'] = explode(',', $conditions['excludeIds']);
        }

        if (!empty($conditions['keyword'])) {
            $conditions['stem'] = trim($conditions['keyword']);
        }

        $replace = empty($conditions['replace']) ? '' : $conditions['replace'];

        $paginator = new Paginator(
            $request,
            $this->getQuestionService()->searchCount($conditions),
            7
        );

        $questions = $this->getQuestionService()->search(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $targets = $this->get('topxia.target_helper')->getTargets(ArrayToolkit::column($questions, 'target'));

        return $this->render('WebBundle:QuestionManage:question-picker.html.twig', array(
            'course'     => $course,
            'questions'  => $questions,
            'replace'    => $replace,
            'paginator'  => $paginator,
            'targets'    => $targets,
            'conditions' => $conditions
        ));
    }

    public function PickedQuestionAction(Request $request, $courseId, $questionId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $question = $this->getQuestionService()->get($questionId);

        if (empty($question)) {
            throw $this->ResourceNotFoundException('question', $questionId);
        }

        $subQuestions = array();

        $targets = $this->get('topxia.target_helper')->getTargets(array($question['target']));

        return $this->render('WebBundle:QuestionManage:question-tr.html.twig', array(
            'courseId'     => $course['id'],
            'question'     => $question,
            'subQuestions' => $subQuestions,
            'targets'      => $targets,
            'type'         => $question['type']
        ));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
