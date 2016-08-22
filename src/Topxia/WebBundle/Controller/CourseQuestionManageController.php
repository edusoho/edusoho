<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Question\QuestionService;
use Symfony\Component\HttpFoundation\Request;

class CourseQuestionManageController extends BaseController
{
    public function indexAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $conditions = $request->query->all();

        if (empty($conditions['target'])) {
            $conditions['targetPrefix'] = "course-{$course['id']}";
        }

        if (!empty($conditions['keyword'])) {
            $conditions['stem'] = $conditions['keyword'];
        }

        if (!empty($conditions['parentId'])) {
            $parentQuestion = $this->getQuestionService()->getQuestion($conditions['parentId']);

            if (empty($parentQuestion)) {
                return $this->redirect($this->generateUrl('course_manage_question', array('courseId' => $courseId)));
            }

            $orderBy = array('createdTime', 'ASC');
        } else {
            $conditions['parentId'] = 0;
            $parentQuestion         = null;
            $orderBy                = array('createdTime', 'DESC');
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getQuestionService()->searchQuestionsCount($conditions),
            10
        );

        $questions = $this->getQuestionService()->searchQuestions(
            $conditions,
            $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($questions, 'userId'));

        $targets = $this->get('topxia.target_helper')->getTargets(ArrayToolkit::column($questions, 'target'));

        return $this->render('TopxiaWebBundle:CourseQuestionManage:index.html.twig', array(
            'course'         => $course,
            'questions'      => $questions,
            'users'          => $users,
            'targets'        => $targets,
            'paginator'      => $paginator,
            'parentQuestion' => $parentQuestion,
            'conditions'     => $conditions,
            'targetChoices'  => $this->getQuestionTargetChoices($course)
        ));
    }

    public function createAction(Request $request, $courseId, $type)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        if ($request->getMethod() == 'POST') {
            $data       = $request->request->all();
            $attachment = $request->request->get('attachment');

            $question = $this->getQuestionService()->createQuestion($data);

            $this->getUploadFileService()->createUseFiles($attachment['stem']['fileIds'], $question['id'], $attachment['stem']['targetType'], $attachment['stem']['type']);
            $this->getUploadFileService()->createUseFiles($attachment['analysis']['fileIds'], $question['id'], $attachment['analysis']['targetType'], $attachment['analysis']['type']);

            if ($data['submission'] == 'continue') {
                $urlParams             = ArrayToolkit::parts($question, array('target', 'difficulty', 'parentId'));
                $urlParams['type']     = $type;
                $urlParams['courseId'] = $courseId;
                $urlParams['goto']     = $request->query->get('goto', null);
                $this->setFlashMessage('success', '题目添加成功，请继续添加。');
                return $this->redirect($this->generateUrl('course_manage_question_create', $urlParams));
            } elseif ($data['submission'] == 'continue_sub') {
                $this->setFlashMessage('success', '题目添加成功，请继续添加子题。');
                return $this->redirect($request->query->get('goto', $this->generateUrl('course_manage_question', array('courseId' => $courseId, 'parentId' => $question['id']))));
            } else {
                $this->setFlashMessage('success', '题目添加成功。');
                return $this->redirect($request->query->get('goto', $this->generateUrl('course_manage_question', array('courseId' => $courseId))));
            }
        }

        $question = array(
            'id'         => 0,
            'type'       => $type,
            'target'     => $request->query->get('target'),
            'difficulty' => $request->query->get('difficulty', 'normal'),
            'parentId'   => $request->query->get('parentId', 0)
        );

        if ($question['parentId'] > 0) {
            $parentQuestion = $this->getQuestionService()->getQuestion($question['parentId']);

            if (empty($parentQuestion)) {
                return $this->createMessageResponse('error', '父题不存在，不能创建子题！');
            }
        } else {
            $parentQuestion = null;
        }

        if ($this->container->hasParameter('enabled_features')) {
            $features = $this->container->getParameter('enabled_features');
        } else {
            $features = array();
        }

        $enabledAudioQuestion = in_array('audio_question', $features);

        return $this->render("TopxiaWebBundle:CourseQuestionManage:question-form-{$type}.html.twig", array(
            'course'               => $course,
            'question'             => $question,
            'parentQuestion'       => $parentQuestion,
            'targetsChoices'       => $this->getQuestionTargetChoices($course),
            'categoryChoices'      => $this->getQuestionCategoryChoices($course),
            'enabledAudioQuestion' => $enabledAudioQuestion
        ));
    }

    public function updateAction(Request $request, $courseId, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        if ($request->getMethod() == 'POST') {
            $question   = $request->request->all();
            $attachment = $request->request->get('attachment');

            $question = $this->getQuestionService()->updateQuestion($id, $question);
            $this->getUploadFileService()->createUseFiles($attachment['stem']['fileIds'], $question['id'], $attachment['stem']['targetType'], $attachment['stem']['type']);
            $this->getUploadFileService()->createUseFiles($attachment['analysis']['fileIds'], $question['id'], $attachment['analysis']['targetType'], $attachment['analysis']['type']);

            $this->setFlashMessage('success', '题目修改成功！');

            return $this->redirect($request->query->get('goto', $this->generateUrl('course_manage_question', array('courseId' => $courseId, 'parentId' => $question['parentId']))));
        }

        $question = $this->getQuestionService()->getQuestion($id);

        if ($question['parentId'] > 0) {
            $parentQuestion = $this->getQuestionService()->getQuestion($question['parentId']);
        } else {
            $parentQuestion = null;
        }

        return $this->render("TopxiaWebBundle:CourseQuestionManage:question-form-{$question['type']}.html.twig", array(
            'course'          => $course,
            'question'        => $question,
            'parentQuestion'  => $parentQuestion,
            'targetsChoices'  => $this->getQuestionTargetChoices($course),
            'categoryChoices' => $this->getQuestionCategoryChoices($course)
        ));
    }

    public function deleteAction(Request $request, $courseId, $id)
    {
        $course   = $this->getCourseService()->tryManageCourse($courseId);
        $question = $this->getQuestionService()->getQuestion($id);
        $this->getQuestionService()->deleteQuestion($id);

        return $this->createJsonResponse(true);
    }

    public function deletesAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $ids = $request->request->get('ids');

        foreach ($ids ?: array() as $id) {
            $this->getQuestionService()->deleteQuestion($id);
        }

        return $this->createJsonResponse(true);
    }

    /**
     * @todo refact it, to xxvholic.
     */
    public function previewAction(Request $request, $courseId, $id)
    {
        $isNewWindow = $request->query->get('isNew');

        $course = $this->getCourseService()->tryManageCourse($courseId);

        $question = $this->getQuestionService()->getQuestion($id);

        if (empty($question)) {
            throw $this->createNotFoundException('题目不存在！');
        }

        $item = array(
            'questionId'   => $question['id'],
            'questionType' => $question['type'],
            'question'     => $question
        );

        if ($question['subCount'] > 0) {
            $questions = $this->getQuestionService()->findQuestionsByParentId($id);

            foreach ($questions as $value) {
                $items[] = array(
                    'questionId'   => $value['id'],
                    'questionType' => $value['type'],
                    'question'     => $value
                );
            }

            $item['items'] = $items;
        }

        $type            = in_array($question['type'], array('single_choice', 'uncertain_choice')) ? 'choice' : $question['type'];
        $questionPreview = true;

        if ($isNewWindow) {
            return $this->render('TopxiaWebBundle:QuizQuestionTest:question-preview.html.twig', array(
                'item'            => $item,
                'type'            => $type,
                'questionPreview' => $questionPreview
            ));
        }

        return $this->render('TopxiaWebBundle:QuizQuestionTest:question-preview-modal.html.twig', array(
            'item'            => $item,
            'type'            => $type,
            'questionPreview' => $questionPreview
        ));
    }

    protected function getQuestionTargetChoices($course)
    {
        $lessons                           = $this->getCourseService()->getCourseLessons($course['id']);
        $choices                           = array();
        $choices["course-{$course['id']}"] = '本课程';

        foreach ($lessons as $lesson) {
            if ($lesson['type'] == 'testpaper') {
                continue;
            }

            $choices["course-{$course['id']}/lesson-{$lesson['id']}"] = "课时{$lesson['number']}：{$lesson['title']}";
        }

        return $choices;
    }

    protected function getQuestionCategoryChoices($course)
    {
        $categories = $this->getQuestionService()->findCategoriesByTarget("course-{$course['id']}", 0, QuestionService::MAX_CATEGORY_QUERY_COUNT);
        $choices    = array();

        foreach ($categories as $category) {
            $choices[$category['id']] = $category['name'];
        }

        return $choices;
    }

    public function uploadAttachmentsAction(Request $request, $id, $targetType)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $storageSetting = $this->getSettingService()->get('storage', array());
        return $this->render('TopxiaWebBundle:CourseQuestionManage:modal-upload-question-attachment.html.twig', array(
            'course'         => $course,
            'storageSetting' => $storageSetting,
            'targetType'     => $targetType,
            'targetId'       => $id
        ));
    }

    public function attachmentDownloadAction(Request $request, $id, $fileId)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($id);

        $file = $this->getUploadFileService()->getFile($fileId);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if ($id != $file["targetId"] || $file['targetType'] != 'coursequestion') {
            throw $this->createNotFoundException();
        }

        return $this->forward("TopxiaWebBundle:UploadFile:download", array(
            'request' => $request,
            'fileId'  => $fileId
        ));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}
