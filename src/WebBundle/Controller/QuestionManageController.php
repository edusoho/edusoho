<?php

namespace WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Exception\ResourceNotFoundException;

class QuestionManageController extends BaseController
{
    public function indexAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $conditions = $request->query->all();

        $conditions['courseId'] = $courseId;
        $conditions['parentId'] = empty($conditions['parentId']) ? 0 : $conditions['parentId'];

        if (!empty($conditions['keyword'])) {
            $conditions['stem'] = $conditions['keyword'];
        }

        if (!empty($conditions['type']) && $conditions['type'] == 0) {
            unset($conditions['type']);
        }

        if (!empty($conditions['target'])) {
            $conditions['lessonId'] = $conditions['target'];
            unset($conditions['target']);
        }

        $parentQuestion = array();
        $orderBy        = array('createdTime' => 'DESC');
        if ($conditions['parentId'] > 0) {
            $parentQuestion = $this->getQuestionService()->get($conditions['parentId']);
            $orderBy        = array('createdTime' => 'ASC');
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

        $courseTasks = $this->getCourseTaskService()->findTasksByCourseId($courseId);
        $courseTasks = ArrayToolkit::index($courseTasks, 'id');

        return $this->render('WebBundle:QuestionManage:index.html.twig', array(
            'course'         => $course,
            'questions'      => $questions,
            'users'          => $users,
            'paginator'      => $paginator,
            'parentQuestion' => $parentQuestion,
            'conditions'     => $conditions,
            'questionTypes'  => $questionTypes,
            'courseTasks'    => $courseTasks
        ));
    }

    public function createAction(Request $request, $courseId, $type)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();

            $data['courseId'] = $courseId;

            $question = $this->getQuestionService()->create($data);

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
            'request'  => $request,
            'courseId' => $course['id'],
            'type'     => $type
        ));
    }

    public function updateAction(Request $request, $courseId, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $question = $this->getQuestionService()->get($id);
        if (!$question) {
            throw new ResourceNotFoundException('question', $id);
        }

        if ($request->getMethod() == 'POST') {
            $question = $request->request->all();
            $this->getQuestionService()->update($id, $question);

            $this->setFlashMessage('success', $this->getServiceKernel()->trans('题目修改成功！'));

            return $this->redirect($request->query->get('goto', $this->generateUrl('course_manage_question', array('courseId' => $courseId, 'parentId' => $question['parentId']))));
        }

        $questionConfig   = $this->getQuestionService()->getQuestionConfig($question['type']);
        $createController = $questionConfig->getAction('edit');

        return $this->forward($createController, array(
            'request'    => $request,
            'courseId'   => $course['id'],
            'questionId' => $question['id']
        ));
    }

    public function deleteAction(Request $request, $courseId, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $this->getQuestionService()->delete($id);

        return $this->createJsonResponse(true);
    }

    public function deletesAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $ids = $request->request->get('ids');

        $this->getQuestionService()->batchDeletes($ids);

        return $this->createJsonResponse(true);
    }

    public function previewAction(Request $request, $courseId, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $isNewWindow = $request->query->get('isNew');

        $question = $this->getQuestionService()->get($id);

        if (empty($question)) {
            throw new ResourceNotFoundException('question', $id);
        }

        $questionTypeObj      = $this->getQuestionService()->getQuestionConfig($question['type']);
        $question['template'] = $questionTypeObj->getTemplate('do');

        if (!empty($question['matas']['mediaId'])) {
            $questionExtends = $questionTypeObj->get($question['matas']['mediaId']);
            $question        = array_merge_recursive($question, $questionExtends);
        }

        if ($question['subCount'] > 0) {
            $questionSubs = $this->getQuestionService()->findQuestionsByParentId($id);

            foreach ($questionSubs as $key => $questionSub) {
                $questionTypeObj = $this->getQuestionService()->getQuestionConfig($questionSub['type']);

                $questionSubs[$key]['template'] = $questionTypeObj->getTemplate('do');
            }

            $question['items'] = $questionSubs;
        }

        $template = 'WebBundle:QuestionManage:preview-modal.html.twig';
        if ($isNewWindow) {
            $template = 'WebBundle:QuestionManage:preview.html.twig';
        }

        return $this->render($template, array(
            'question'     => $question,
            'showAnswer'   => 1,
            'showAnalysis' => 1
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

    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCourseTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
