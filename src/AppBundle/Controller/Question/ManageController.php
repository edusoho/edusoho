<?php

namespace AppBundle\Controller\Question;

use AppBundle\Common\Paginator;
use Biz\Content\Service\FileService;
use Biz\Question\QuestionException;
use Biz\Task\Service\TaskService;
use Biz\User\Service\TokenService;
use Biz\User\Service\UserService;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseSetService;
use ExamParser\Writer\WriteDocx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Topxia\Service\Common\ServiceKernel;
use Biz\Question\Service\QuestionService;
use Symfony\Component\HttpFoundation\Request;

class ManageController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        $sync = $request->query->get('sync');
        if ($courseSet['locked'] && empty($sync)) {
            return $this->redirectToRoute('course_set_manage_sync', array(
                'id' => $id,
                'sideNav' => 'question',
            ));
        }

        $conditions = $request->query->all();

        $conditions['courseSetId'] = $courseSet['id'];
        $conditions['parentId'] = empty($conditions['parentId']) ? 0 : $conditions['parentId'];

        $parentQuestion = array();
        $orderBy = array('createdTime' => 'DESC');
        if ($conditions['parentId'] > 0) {
            $parentQuestion = $this->getQuestionService()->get($conditions['parentId']);
            $orderBy = array('createdTime' => 'ASC');
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

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($questions, 'updatedUserId'));

        $taskIds = ArrayToolkit::column($questions, 'lessonId');
        $courseTasks = $this->getTaskService()->findTasksByIds($taskIds);
        $courseTasks = ArrayToolkit::index($courseTasks, 'id');

        $courseIds = ArrayToolkit::column($questions, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $user = $this->getUser();
        $searchCourses = $this->getCourseService()->findUserManageCoursesByCourseSetId($user['id'], $courseSet['id']);
        $conditions = array(
            'courseId' => $request->query->get('courseId', 0),
            'typesNotIn' => array('testpaper', 'homework', 'exercise'),
        );
        $showTasks = $this->getTaskService()->searchTasks($conditions, array(), 0, PHP_INT_MAX);
        $showTasks = ArrayToolkit::index($showTasks, 'id');

        return $this->render('question-manage/index.html.twig', array(
            'courseSet' => $courseSet,
            'questions' => $questions,
            'users' => $users,
            'paginator' => $paginator,
            'parentQuestion' => $parentQuestion,
            'conditions' => $conditions,
            'courseTasks' => $courseTasks,
            'courses' => $courses,
            'searchCourses' => $searchCourses,
            'showTasks' => $showTasks,
        ));
    }

    public function readAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        return $this->forward('AppBundle:Question/QuestionParser:read', array(
            'request' => $request,
            'type' => 'question',
            'courseSet' => $courseSet,
        ));
    }

    public function exportAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);
        $fields = $request->query->all();

        $conditions = ArrayToolkit::parts($fields, array('type', 'courseId', 'keyword', 'lessonId'));
        $conditions['courseSetId'] = $courseSet['id'];
        $conditions['parentId'] = 0;

        $questionCount = $this->getQuestionService()->searchCount($conditions);

        $questions = $this->getQuestionService()->search(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            $questionCount
        );

        if (empty($questions)) {
            return $this->createMessageResponse('info', '导出题目为空', null, 3000, $this->generateUrl('course_set_manage_question', array('id' => $id)));
        }

        $questions = $this->buildExportQuestions($questions);

        $fileName = str_replace(',', '', $courseSet['title']).'-题目.docx';
        $baseDir = $this->get('kernel')->getContainer()->getParameter('topxia.disk.local_directory');
        $path = $baseDir.DIRECTORY_SEPARATOR.$fileName;

        $writer = new WriteDocx($path);
        $writer->write($questions);

        $headers = array(
            'Content-Type' => 'application/msword',
            'Content-Disposition' => 'attachment; filename='.$fileName,
        );

        return new BinaryFileResponse($path, 200, $headers);
    }

    public function createAction(Request $request, $id, $type)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        if ('POST' === $request->getMethod()) {
            $data = $request->request->all();

            $data['courseSetId'] = $courseSet['id'];

            $question = $this->getQuestionService()->create($data);

            if ('continue' === $data['submission']) {
                $urlParams = ArrayToolkit::parts($question, array('target', 'difficulty', 'parentId'));
                $urlParams['type'] = $type;
                $urlParams['id'] = $courseSet['id'];
                $urlParams['goto'] = $request->query->get('goto', null);
                $this->setFlashMessage('success', 'site.add.success');

                return $this->redirect($this->generateUrl('course_set_manage_question_create', $urlParams));
            }
            if ('continue_sub' === $data['submission']) {
                $this->setFlashMessage('success', 'site.add.success');

                return $this->redirect(
                    $request->query->get(
                        'goto',
                        $this->generateUrl(
                            'course_set_manage_question',
                            array('id' => $courseSet['id'], 'parentId' => $question['id'])
                        )
                    )
                );
            }

            $this->setFlashMessage('success', 'site.add.success');

            return $this->redirect(
                $request->query->get(
                    'goto',
                    $this->generateUrl(
                        'course_set_manage_question',
                        array('id' => $courseSet['id'], 'parentId' => $question['parentId'])
                    )
                )
            );
        }

        $questionConfig = $this->getQuestionConfig();
        $createController = $questionConfig[$type]['actions']['create'];

        return $this->forward($createController, array(
            'request' => $request,
            'courseSetId' => $courseSet['id'],
            'type' => $type,
        ));
    }

    public function updateAction(Request $request, $courseSetId, $questionId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $question = $this->getQuestionService()->get($questionId);
        if (!$question || $question['courseSetId'] != $courseSetId) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }

        if ('POST' === $request->getMethod()) {
            $fields = $request->request->all();
            $this->getQuestionService()->update($question['id'], $fields);

            $this->setFlashMessage('success', 'site.save.success');

            return $this->redirect(
                $request->query->get(
                    'goto',
                    $this->generateUrl(
                        'course_set_manage_question',
                        array('id' => $courseSet['id'], 'parentId' => $question['parentId'])
                    )
                )
            );
        }

        $questionConfig = $this->getQuestionConfig();
        $createController = $questionConfig[$question['type']]['actions']['edit'];

        return $this->forward($createController, array(
            'request' => $request,
            'courseSetId' => $courseSet['id'],
            'questionId' => $question['id'],
        ));
    }

    public function deleteAction(Request $request, $courseSetId, $questionId)
    {
        $this->getCourseSetService()->tryManageCourseSet($courseSetId);
        $question = $this->getQuestionService()->get($questionId);
        if (!$question || $question['courseSetId'] != $courseSetId) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }
        $this->getQuestionService()->delete($questionId);

        return $this->createJsonResponse(true);
    }

    public function deletesAction(Request $request, $courseSetId)
    {
        $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $ids = $request->request->get('ids', array());
        $questions = $this->getQuestionService()->findQuestionsByIds($ids);
        if (empty($questions)) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }
        foreach ($questions as $question) {
            if ($question['courseSetId'] != $courseSetId) {
                $this->createNewException(QuestionException::NOTFOUND_QUESTION());
            }
        }
        $this->getQuestionService()->batchDeletes($ids);

        return $this->createJsonResponse(true);
    }

    public function previewAction(Request $request, $courseSetId, $questionId)
    {
        $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $isNewWindow = $request->query->get('isNew');

        $question = $this->getQuestionService()->get($questionId);

        if (!$question || $question['courseSetId'] != $courseSetId) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }

        if (!empty($question['matas']['mediaId'])) {
            $questionTypeObj = $this->getQuestionService()->getQuestionConfig($question['type']);
            $questionExtends = $questionTypeObj->get($question['matas']['mediaId']);
            $question = array_merge_recursive($question, $questionExtends);
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
            'question' => $question,
            'showAnswer' => 1,
            'showAnalysis' => 1,
        ));
    }

    public function checkAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);
        $conditions = $request->request->all();
        $conditions['courseSetId'] = $courseSet['id'];

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

        $conditions['parentId'] = 0;
        $conditions['courseSetId'] = $courseSet['id'];

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

        $user = $this->getUser();
        $manageCourses = $this->getCourseService()->findUserManageCoursesByCourseSetId($user['id'], $courseSet['id']);

        return $this->render('question-manage/question-picker.html.twig', array(
            'courseSet' => $courseSet,
            'questions' => $questions,
            'replace' => empty($conditions['replace']) ? '' : $conditions['replace'],
            'paginator' => $paginator,
            'courseTasks' => $this->getQuestionRanges($request->query->get('courseId', 0)),
            'conditions' => $conditions,
            'targetType' => $request->query->get('targetType', 'testpaper'),
            'courses' => $manageCourses,
        ));
    }

    public function pickedQuestionAction(Request $request, $courseSetId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $questionIds = $request->request->get('questionIds', array(0));

        if (!$questionIds) {
            return $this->createJsonResponse(array('result' => 'error', 'message' => '请先选择题目'));
        }

        $questions = $this->getQuestionService()->findQuestionsByIds($questionIds);

        foreach ($questions as &$question) {
            if ($question['courseSetId'] != $courseSetId) {
                $this->createNewException(QuestionException::NOTFOUND_QUESTION());
            }
            if ($question['subCount'] > 0) {
                $question['subs'] = $this->getQuestionService()->findQuestionsByParentId($question['id']);
            }
        }

        $user = $this->getUser();
        $manageCourses = $this->getCourseService()->findUserManageCoursesByCourseSetId($user['id'], $courseSet['id']);
        $taskIds = ArrayToolkit::column($questions, 'lessonId');
        $courseTasks = $this->getTaskService()->findTasksByIds($taskIds);
        $courseTasks = ArrayToolkit::index($courseTasks, 'id');

        return $this->render('question-manage/question-picked.html.twig', array(
            'courseSet' => $courseSet,
            'questions' => $questions,
            'targetType' => $request->query->get('targetType', 'testpaper'),
            'courseTasks' => $courseTasks,
            'courses' => $manageCourses,
        ));
    }

    public function showTasksAction(Request $request, $courseSetId)
    {
        $courseId = $request->request->get('courseId', 0);
        if (empty($courseId)) {
            return $this->createJsonResponse(array());
        }

        $this->getCourseService()->tryManageCourse($courseId);

        $conditions = array(
            'courseId' => $courseId,
            'typesNotIn' => array('testpaper', 'homework', 'exercise'),
        );
        $courseTasks = $this->getTaskService()->searchTasks($conditions, array(), 0, PHP_INT_MAX);

        return $this->createJsonResponse($courseTasks);
    }

    public function showQuestionTypesNumAction(Request $request, $courseSetId)
    {
        $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $conditions = $request->request->all();
        $conditions['courseSetId'] = $courseSetId;
        $conditions['parentId'] = 0;

        $typesNum = $this->getQuestionService()->getQuestionCountGroupByTypes($conditions);
        $typesNum = ArrayToolkit::index($typesNum, 'type');

        return $this->createJsonResponse($typesNum);
    }

    public function reEditAction(Request $request, $token)
    {
        return $this->forward('AppBundle:Question/QuestionParser:reEdit', array(
            'request' => $request,
            'token' => $token,
            'type' => 'question',
        ));
    }

    public function saveImportQuestionsAction(Request $request, $token)
    {
        $token = $this->getTokenService()->verifyToken('upload.course_private_file', $token);
        $data = $token['data'];
        $this->getCourseSetService()->tryManageCourseSet($data['courseSetId']);
        $content = $request->getContent();
        $postData = json_decode($content, true);
        $this->getQuestionService()->importQuestions($postData['questions'], $token);

        return $this->createJsonResponse(true);
    }

    protected function buildExportQuestions($questions)
    {
        $exportQuestions = array();
        $wrapper = $this->getWrapper();

        $seq = 1;
        $num = 1;
        foreach ($questions as $question) {
            $question['seq'] = $seq++;
            $question['num'] = $num++;
            if ('material' == $question['type']) {
                $subQuestions = $this->getQuestionService()->findQuestionsByParentId($question['id']);
                $subSeq = 1;
                foreach ($subQuestions as $index => $subQuestion) {
                    $subQuestions[$index]['seq'] = $subSeq++;
                }
                $question['subs'] = $subQuestions;
            }

            $question = $wrapper->handle($question, 'exportQuestion');
            $question = ArrayToolkit::parts($question, array(
                'type',
                'seq',
                'stem',
                'options',
                'answer',
                'score',
                'difficulty',
                'analysis',
                'subs',
                'num',
            ));
            $exportQuestions[] = $question;
        }

        return $exportQuestions;
    }

    protected function getWrapper()
    {
        global $kernel;

        return $kernel->getContainer()->get('web.wrapper');
    }

    protected function getQuestionConfig()
    {
        return $this->get('extension.manager')->getQuestionTypes();
    }

    protected function getQuestionRanges($courseId)
    {
        if (empty($courseId)) {
            return array();
        }

        $courseTasks = $this->getTaskService()->findTasksByCourseId($courseId);

        return ArrayToolkit::index($courseTasks, 'id');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return ServiceKernel
     */
    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }
}
