<?php

namespace AppBundle\Controller\Course;

use AppBundle\Controller\BaseController;
use AppBundle\Util\UploaderToken;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\LessonService;
use Biz\File\Service\UploadFileService;
use Symfony\Component\HttpFoundation\Request;

class LessonManageController extends BaseController
{
    public function createAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $this->getCourseLessonService()->isLessonCountEnough($course['id']);

        if ($request->isMethod('POST')) {
            $formData = $request->request->all();

            $formData['_base_url'] = $request->getSchemeAndHttpHost();
            $formData['fromUserId'] = $this->getUser()->getId();
            $formData['fromCourseSetId'] = $course['courseSetId'];
            $formData['redoInterval'] = empty($formData['redoInterval']) ? 0 : $formData['redoInterval'] * 60;

            $formData = array_merge($this->getDefaultFinishCondition($formData['mediaType']), $formData);
            list($lesson, $task) = $this->getCourseLessonService()->createLesson($formData);

            return $this->getTaskJsonView($course, $task);
        }

        return $this->forward('AppBundle:TaskManage:create', ['courseId' => $course['id']]);
    }

    private function getDefaultFinishCondition($mediaType)
    {
        $activityConfigManager = $this->get('activity_config_manager');
        $activityConfig = $activityConfigManager->getInstalledActivity($mediaType);
        if (empty($activityConfig['finish_condition'])) {
            return [];
        }
        $finishCondition = reset($activityConfig['finish_condition']);

        return [
            'finishType' => $finishCondition['type'],
            'finishData' => empty($finishCondition['value']) ? '' : $finishCondition['value'],
        ];
    }

    public function batchCreateAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $mode = $request->query->get('mode');
        $this->getCourseLessonService()->isLessonCountEnough($course['id']);
        if ($request->isMethod('POST')) {
            $fileId = $request->request->get('fileId');
            $file = $this->getUploadFileService()->getFile($fileId);

            if (!in_array($file['type'], ['document', 'video', 'audio', 'ppt', 'flash'])) {
                return $this->createJsonResponse(['error' => '不支持的文件类型']);
            }
            $formData = $this->createTaskByFileAndCourse($file, $course);
            $formData['mode'] = $mode;
            $formData['_base_url'] = $request->getSchemeAndHttpHost();

            $defaultFinishCondition = $this->getDefaultFinishCondition($formData['mediaType']);
            $formData = array_merge($defaultFinishCondition, $formData);

            list($lesson, $task) = $this->getCourseLessonService()->createLesson($formData);

            return $this->getTaskJsonView($course, $task);
        }

        $token = $request->query->get('token');
        $parser = new UploaderToken();
        $params = $parser->parse($token);

        if (!$params) {
            return $this->createJsonResponse(['error' => 'bad token']);
        }

        $lessonCount = $this->getCourseLessonService()->countLessons(['courseId' => $course['id']]);
        $enableLessonCount = $this->getCourseLessonService()->getLessonLimitNum() - $lessonCount;

        return $this->render(
            'course-manage/batch-create/batch-create-modal.html.twig',
            [
                'token' => $token,
                'targetType' => $params['targetType'],
                'courseId' => $courseId,
                'mode' => $mode,
                'enableLessonCount' => $enableLessonCount,
            ]
        );
    }

    public function validLessonNumAction(Request $request, $courseId)
    {
        $uploadLessonNum = $request->request->get('number');
        $lessonCount = $this->getCourseLessonService()->countLessons(['courseId' => $courseId]);
        $lessonLimitNum = $this->getCourseLessonService()->getLessonLimitNum();
        if ($beyondNum = $lessonLimitNum - $lessonCount - $uploadLessonNum < 0) {
            return $this->createJsonResponse(['error' => '上传文件数量超出', 'beyondNum' => $beyondNum]);
        }

        return $this->createJsonResponse(['success' => true]);
    }

    public function updateAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $lesson = $this->getCourseService()->getChapter($courseId, $lessonId);

        if ('POST' == $request->getMethod()) {
            $fields = $request->request->all();
            $lesson = $this->getCourseLessonService()->updateLesson($lesson['id'], $fields);

            return $this->render('lesson-manage/chapter/item.html.twig', [
                'course' => $course,
                'chapter' => $lesson,
            ]);
        }

        return $this->render('lesson-manage/chapter/modal.html.twig', [
            'course' => $course,
            'type' => 'lesson',
            'chapter' => $lesson,
        ]);
    }

    public function publishAction(Request $request, $courseId, $lessonId)
    {
        $this->getCourseLessonService()->publishLesson($courseId, $lessonId);

        return $this->createJsonResponse(['success' => true]);
    }

    public function unpublishAction(Request $request, $courseId, $lessonId)
    {
        $this->getCourseLessonService()->unpublishLesson($courseId, $lessonId);

        return $this->createJsonResponse(['success' => true]);
    }

    public function deleteAction(Request $request, $courseId, $lessonId)
    {
        $this->getCourseLessonService()->deleteLesson($courseId, $lessonId);

        return $this->createJsonResponse(['success' => true]);
    }

    public function setOptionalAction(Request $request, $courseId, $lessonId)
    {
        $this->getCourseLessonService()->setOptional($courseId, $lessonId);

        return $this->createJsonResponse(['success' => true]);
    }

    public function unsetOptionalAction(Request $request, $courseId, $lessonId)
    {
        $this->getCourseLessonService()->unsetOptional($courseId, $lessonId);

        return $this->createJsonResponse(['success' => true]);
    }

    private function createTaskByFileAndCourse($file, $course)
    {
        $task = [
            'mediaType' => $file['type'],
            'fromCourseId' => $course['id'],
            'fromUserId' => $this->getUser()->getId(),
            'fromCourseSetId' => $course['courseSetId'],
            'courseSetType' => 'normal',
            'media' => json_encode(['source' => 'self', 'id' => $file['id'], 'name' => $file['filename']]),
            'type' => $file['type'],
            'length' => $file['length'],
            'title' => str_replace(strrchr($file['filename'], '.'), '', $file['filename']),
            'ext' => ['mediaSource' => 'self', 'mediaId' => $file['id']],
            'categoryId' => 0,
        ];
        if ('document' == $file['type']) {
            $task['type'] = 'doc';
            $task['mediaType'] = 'doc';
        }

        return $task;
    }

    //创建任务或修改任务返回的html
    protected function getTaskJsonView($course, $task)
    {
        $taskJsonData = $this->createCourseStrategy($course)->getTasksJsonData($task);
        if (empty($taskJsonData)) {
            return $this->createJsonResponse(false);
        }

        return $this->createJsonResponse($this->renderView(
            $taskJsonData['template'],
            $taskJsonData['data']
        ));
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
     * @return LessonService
     */
    protected function getCourseLessonService()
    {
        return $this->createService('Course:LessonService');
    }

    protected function createCourseStrategy($course)
    {
        return $this->getBiz()->offsetGet('course.strategy_context')->createStrategy($course['courseType']);
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }
}
