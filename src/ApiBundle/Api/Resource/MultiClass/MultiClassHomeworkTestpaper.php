<?php

namespace ApiBundle\Api\Resource\MultiClass;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\HomeworkActivityService;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassProductService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class MultiClassHomeworkTestpaper extends AbstractResource
{
    public function search(ApiRequest $request, $multiClassId)
    {
        $multiClass = $this->getMultiClassService()->getMultiClass($multiClassId);
        if (empty($multiClass)) {
            throw MultiClassException::MULTI_CLASS_NOT_EXIST();
        }

        $course = $this->getCourseService()->getCourse($multiClass['courseId']);
        if (empty($course)) {
            throw CourseException::NOTFOUND_COURSE();
        }

        $conditions = [
            'courseId' => $course['id'],
            'types' => $request->query->get('types', ['testpaper', 'homework'])
        ];
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $tasks = $this->getCourseService()->searchMultiClassCourseItems($conditions, [], $offset, $limit);
        $tasks = $this->getCourseTaskTree($tasks, $course, $request->getHttpRequest()->isSecure());

        $testpapers = $this->findTestpapers($tasks);
        $testpapersStatusNum = $this->findTestpapersStatusNum($tasks);

        return [
            'lessons' => $tasks,
            'assessments' => $testpapers,
            'assessmentStatusNum' => $testpapersStatusNum
        ];
    }

    protected function getCourseTaskTree($tasks, $course, $isSsl)
    {
        $items = $this->convertToLeadingItems($tasks, $course, $isSsl, 0);
        $items = $this->convertToTree($items);

        $necessaryTasks = [];
        foreach ($items as $item){
            $units = $item['children'];
            foreach ($units as $unit){
                $lessons = $unit['children'];
                foreach ($lessons as &$lesson){
                    if ($lesson['isExist']){
                        foreach ($lesson['tasks'] as $key => &$task){
                            $task['chapterTitle'] = $item['title'];
                            $task['unitTitle'] = $unit['title'];
                            $task['answerSceneId'] = $task['activity']['ext']['answerSceneId'];
                            if ($task['type'] === 'homework'){
                                $task['assessmentId'] = $task['activity']['ext']['assessmentId'];
                            }else{
                                $task['assessmentId'] = $task['activity']['ext']['mediaId'];
                            }
                            unset($task['activity']['ext']);
                            $necessaryTasks[] = $task;
                        }
                    }
                }
            }
        }

        return $necessaryTasks;
    }

    protected function findTestpapers($tasks)
    {
        if (empty($tasks)) {
            return [$tasks, []];
        }

        $ids = ArrayToolkit::column($tasks, 'assessmentId');
        $testpapers = $this->getAssessmentService()->findAssessmentsByIds($ids);

        return empty($testpapers) ? [] : $testpapers;
    }

    protected function findTestpapersStatusNum($tasks)
    {
        $resultStatusNum = [];
        foreach ($tasks as $task) {
            if (empty($task['answerSceneId'])) {
                continue;
            }

            $answerRecords = $this->getAnswerRecordService()->search(
                ['answer_scene_id' => $task['answerSceneId']],
                [],
                0,
                $this->getAnswerRecordService()->count(['answer_scene_id' => $task['answerSceneId']])
            );
            $resultStatusNum[$task['activityId']] = ArrayToolkit::group($answerRecords, 'status');
            foreach ($resultStatusNum[$task['activityId']] as &$status) {
                $status = count($status);
            }
        }

        return $resultStatusNum;
    }

    protected function convertToLeadingItems($originItems, $course, $isSsl, $fetchSubtitlesUrls, $onlyPublishTask = false, $showOptionalNum = 1)
    {
        return $this->container->get('api.util.item_helper')->convertToLeadingItemsV2($originItems, $course, $isSsl, $fetchSubtitlesUrls, $onlyPublishTask, $showOptionalNum);
    }

    protected function convertToTree($items)
    {
        return $this->container->get('api.util.item_helper')->convertToTree($items);
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return HomeworkActivityService
     */
    protected function getHomeworkActivityService()
    {
        return $this->service('Activity:HomeworkActivityService');
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->service('Activity:TestpaperActivityService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->service('MultiClass:MultiClassService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return MultiClassProductService
     */
    protected function getMultiClassProductService()
    {
        return $this->service('MultiClass:MultiClassProductService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->service('Course:MemberService');
    }
}
