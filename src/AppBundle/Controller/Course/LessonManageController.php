<?php

namespace AppBundle\Controller\Course;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use AppBundle\Util\UploaderToken;
use Biz\Course\LessonException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\LessonService;
use Biz\File\Service\UploadFileService;
use Biz\System\Service\SettingService;
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

        if ('video' === $mediaType) {
            $setting = $this->getSettingService()->get('videoEffectiveTimeStatistics');
            $finishType = empty($setting) ? 'end' : ('playing' === $setting['statistical_dimension'] ? 'watchTime' : 'time');
            $activityFinishConditions = array_column($activityConfig['finish_condition'], null, 'type');
            $finishCondition = $activityFinishConditions[$finishType];
        } else {
            $finishCondition = reset($activityConfig['finish_condition']);
        }

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

            if (!in_array($file['type'], ['document', 'video', 'audio', 'ppt'])) {
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
                'targetType' => 'course-batch-create-lesson',
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

    public function validLessonTypeAction(Request $request)
    {
        $fileIds = $request->request->get('fileIds');
        $files = $this->getUploadFileService()->findFilesByIds($fileIds);

        foreach ($files as $file) {
            if ('flash' == $file['type']) {
                $invalidFileIds[] = $file['id'];
            }
        }

        if (!empty($invalidFileIds)) {
            return $this->createJsonResponse(['status' => false, 'invalidFileIds' => $invalidFileIds]);
        }

        return $this->createJsonResponse(['status' => true]);
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
        try {
            $this->getCourseLessonService()->publishLesson($courseId, $lessonId);
        } catch (\Exception $e) {
            return $this->createJsonResponse(['success' => false, 'message' => $this->trans('exception.task.forbidden_publish_sync_task')]);
        }

        return $this->createJsonResponse(['success' => true]);
    }

    public function unpublishAction(Request $request, $courseId, $lessonId)
    {
        try {
            $this->getCourseLessonService()->unpublishLesson($courseId, $lessonId);
        } catch (\Exception $e) {
            return $this->createJsonResponse(['success' => false, 'message' => $this->trans('course.manage.lesson_copy_ing')]);
        }

        return $this->createJsonResponse(['success' => true]);
    }

    public function batchPublishAction(Request $request, $courseId)
    {
        $lessonIds = $request->request->get('lessonIds');

        if (empty($lessonIds)) {
            $this->createNewException(LessonException::PARAMETERS_MISSING());
        }

        $lessons = $this->getCourseLessonService()->batchUpdateLessonsStatus($courseId, $lessonIds, 'published');

        if (empty($lessons)) {
            return $this->createJsonResponse(['success' => true]);
        }

        return $this->createJsonResponse(ArrayToolkit::column($lessons, 'id'));
    }

    public function batchUnpublishAction(Request $request, $courseId)
    {
        $lessonIds = $request->request->get('lessonIds');

        if (empty($lessonIds)) {
            $this->createNewException(LessonException::PARAMETERS_MISSING());
        }

        $lessons = $this->getCourseLessonService()->batchUpdateLessonsStatus($courseId, $lessonIds, 'unpublished');

        if (empty($lessons)) {
            return $this->createJsonResponse(['success' => true]);
        }

        return $this->createJsonResponse(ArrayToolkit::column($lessons, 'id'));
    }

    public function deleteAction(Request $request, $courseId, $lessonId)
    {
        $this->getCourseLessonService()->deleteLesson($courseId, $lessonId);

        return $this->createJsonResponse(['success' => true]);
    }

    public function batchDeleteAction(Request $request, $courseId)
    {
        $lessonIds = $request->request->get('lessonIds');

        if (empty($lessonIds)) {
            $this->createNewException(LessonException::PARAMETERS_MISSING());
        }

        $lessons = $this->getCourseLessonService()->batchDeleteLessons($courseId, $lessonIds);

        if (empty($lessons)) {
            return $this->createJsonResponse(['success' => true]);
        }

        return $this->createJsonResponse(ArrayToolkit::column($lessons, 'id'));
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

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
