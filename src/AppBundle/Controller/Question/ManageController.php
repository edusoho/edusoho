<?php

namespace AppBundle\Controller\Question;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Exception\ResourceNotFoundException;

class ManageController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        $conditions = $request->query->all();

        $conditions['courseId'] = $courseSet['id'];
        $conditions['parentId'] = empty($conditions['parentId']) ? 0 : $conditions['parentId'];

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

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($questions, 'userId'));

        $courseTasks = $this->getCourseTaskService()->findTasksByCourseId($courseSet['id']);
        $courseTasks = ArrayToolkit::index($courseTasks, 'id');

        return $this->render('question-manage/index.html.twig', array(
            'courseSet'      => $courseSet,
            'questions'      => $questions,
            'users'          => $users,
            'paginator'      => $paginator,
            'parentQuestion' => $parentQuestion,
            'conditions'     => $conditions,
            'courseTasks'    => $courseTasks
        ));
    }

    public function createAction(Request $request, $id, $type)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();

            $data['courseId'] = $courseSet['id'];

            $question = $this->getQuestionService()->create($data);

            if ($data['submission'] == 'continue') {
                $urlParams         = ArrayToolkit::parts($question, array('target', 'difficulty', 'parentId'));
                $urlParams['type'] = $type;
                $urlParams['id']   = $courseSet['id'];
                $urlParams['goto'] = $request->query->get('goto', null);
                $this->setFlashMessage('success', $this->getServiceKernel()->trans('题目添加成功，请继续添加。'));
                return $this->redirect($this->generateUrl('course_set_manage_question_create', $urlParams));
            } elseif ($data['submission'] == 'continue_sub') {
                $this->setFlashMessage('success', $this->getServiceKernel()->trans('题目添加成功，请继续添加子题。'));
                return $this->redirect($request->query->get('goto', $this->generateUrl('course_set_manage_question', array('id' => $courseSet['id'], 'parentId' => $question['id']))));
            } else {
                $this->setFlashMessage('success', $this->getServiceKernel()->trans('题目添加成功。'));
                return $this->redirect($request->query->get('goto', $this->generateUrl('course_set_manage_question', array('id' => $courseSet['id']))));
            }
        }

        $questionConfig   = $this->getQuestionConfig();
        $createController = $questionConfig[$type]['actions']['create'];

        return $this->forward($createController, array(
            'request'     => $request,
            'courseSetId' => $courseSet['id'],
            'type'        => $type
        ));
    }

    public function updateAction(Request $request, $courseSetId, $questionId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $question = $this->getQuestionService()->get($questionId);
        if (!$question) {
            throw new ResourceNotFoundException('question', $questionId);
        }

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $this->getQuestionService()->update($question['id'], $fields);

            $this->setFlashMessage('success', $this->getServiceKernel()->trans('题目修改成功！'));

            return $this->redirect($request->query->get('goto', $this->generateUrl('course_set_manage_question', array('id' => $courseSet['id'], 'parentId' => $question['parentId']))));
        }

        $questionConfig   = $this->getQuestionConfig();
        $createController = $questionConfig[$question['type']]['actions']['edit'];

        return $this->forward($createController, array(
            'request'     => $request,
            'courseSetId' => $courseSet['id'],
            'questionId'  => $question['id']
        ));
    }

    public function deleteAction(Request $request, $courseSetId, $questionId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $this->getQuestionService()->delete($questionId);

        return $this->createJsonResponse(true);
    }

    public function deletesAction(Request $request, $courseSetId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $ids = $request->request->get('ids', array());

        $this->getQuestionService()->batchDeletes($ids);

        return $this->createJsonResponse(true);
    }

    public function previewAction(Request $request, $courseSetId, $questionId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $isNewWindow = $request->query->get('isNew');

        $question = $this->getQuestionService()->get($questionId);

        if (empty($question)) {
            throw new ResourceNotFoundException('question', $question['id']);
        }

        $questionConfig       = $this->getQuestionConfig();
        $question['template'] = $questionConfig[$question['type']]['templates']['do'];

        if (!empty($question['matas']['mediaId'])) {
            $questionExtends = $questionTypeObj->get($question['matas']['mediaId']);
            $question        = array_merge_recursive($question, $questionExtends);
        }

        if ($question['subCount'] > 0) {
            $questionSubs = $this->getQuestionService()->findQuestionsByParentId($question['id']);

            $question['subs'] = $questionSubs;
        }

        $template = 'question-manage/preview-modal.html.twig';
        if ($isNewWindow) {
            $template = 'question-manage/preview.html.twig';
        }

        return $this->render($template, array(
            'question'     => $question,
            'showAnswer'   => 1,
            'showAnalysis' => 1
        ));
    }

    public function checkAction(Request $request, $id)
    {
        $courseSet              = $this->getCourseSetService()->tryManageCourseSet($id);
        $conditions             = $request->request->all();
        $conditions['courseId'] = $courseSet['id'];

        if (!empty($conditions['types'])) {
            $conditions['types'] = explode(',', $conditions['types']);
        }

        $count = $this->getQuestionService()->searchCount($conditions);

        $result = false;
        if (!empty($conditions['itemCount']) && $count >= $conditions['itemCount']) {
            $result = true;
        }

        return $this->createJsonResponse($result);
    }

    public function questionPickerAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        $conditions = $request->query->all();

        /*if (empty($conditions['target'])) {
        $conditions['targetPrefix'] = "course-{$courseSet['id']}";
        }*/

        $conditions['parentId'] = 0;

        if (empty($conditions['excludeIds'])) {
            unset($conditions['excludeIds']);
        } else {
            $conditions['excludeIds'] = explode(',', $conditions['excludeIds']);
        }

        if (!empty($conditions['keyword'])) {
            $conditions['stem'] = trim($conditions['keyword']);
        }

        $paginator = new Paginator(
            $request,
            $this->getQuestionService()->searchCount($conditions),
            7
        );

        $questions = $this->getQuestionService()->search(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        /*$targets = $this->get('topxia.target_helper')->getTargets(ArrayToolkit::column($questions, 'target'));*/

        return $this->render('question-manage/question-picker.html.twig', array(
            'courseSet'     => $courseSet,
            'questions'     => $questions,
            'replace'       => empty($conditions['replace']) ? '' : $conditions['replace'],
            'paginator'     => $paginator,
            'targetChoices' => $this->getQuestionRanges($courseSet),
            //'targets'       => $targets,
            'conditions'    => $conditions,
            'target'        => $request->query->get('target', 'testpaper')
        ));
    }

    public function pickedQuestionAction(Request $request, $courseSetId, $questionId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $question = $this->getQuestionService()->get($questionId);

        if (empty($question)) {
            throw $this->ResourceNotFoundException('question', $questionId);
        }

        $subQuestions = array();

        //$targets = $this->get('topxia.target_helper')->getTargets(array($question['target']));

        return $this->render('question-manage/question-item-picked.html.twig', array(
            'courseSet'    => $courseSet,
            'question'     => $question,
            'subQuestions' => $subQuestions,
            //'targets'      => $targets,
            'type'         => $question['type'],
            'target'       => $request->query->get('target', 'testpaper')
        ));
    }

    protected function getQuestionConfig()
    {
        return $this->get('extension.default')->getQuestionTypes();
    }

    protected function getQuestionRanges($course)
    {
        $ranges = array('本课程');

        return $ranges;
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
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
