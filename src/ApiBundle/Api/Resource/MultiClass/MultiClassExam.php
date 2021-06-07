<?php

namespace ApiBundle\Api\Resource\MultiClass;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassService;
use Biz\Task\Service\TaskService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class MultiClassExam extends AbstractResource
{
    /**
     * @param ApiRequest $request
     * @param $multiClassId
     * @return array
     * @Access(roles="ROLE_TEACHER_ASSISTANT,ROLE_TEACHER,ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
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
        $total = $this->getTaskService()->countTasks($conditions);
        $lessons = $this->getCourseTaskTree($tasks, $course, $request->getHttpRequest()->isSecure());
        $lessons = $this->findTestpapersAndStatusNum($lessons);

        return $this->makePagingObject($lessons, $total, $offset, $limit);
    }

    protected function getCourseTaskTree($tasks, $course, $isSsl)
    {
        $items = $this->convertToLeadingItems($tasks, $course, $isSsl, 0);
        $items = $this->convertToTree($items);

        $necessaryLessons = [];
        foreach ($items as $item){
            $units = $item['children'];
            foreach ($units as $unit){
                $lessons = $unit['children'];
                foreach ($lessons as &$lesson){
                    $lesson['chapterTitle'] = $item['title'];
                    $lesson['unitTitle'] = $unit['title'];
                    if ($lesson['isExist']){
                        foreach ($lesson['tasks'] as $key => $task){
                            $task['answerSceneId'] = $task['activity']['ext']['answerSceneId'];
                            if ($task['type'] === 'homework'){
                                $task['assessmentId'] = $task['activity']['ext']['assessmentId'];
                            }else{
                                $task['assessmentId'] = $task['activity']['ext']['mediaId'];
                            }
                            unset($task['activity']['ext']);
                            $lesson['tasks'] = $task;
                            $necessaryLessons[] = $lesson;
                        }
                    }
                }
            }
        }

        return $necessaryLessons;
    }

    protected function findTestpapersAndStatusNum($lessons)
    {
        if (empty($lessons)) {
            return [];
        }

        $tasks = ArrayToolkit::column($lessons, 'tasks');
        $ids = ArrayToolkit::column($tasks, 'assessmentId');
        $testpapers = $this->getAssessmentService()->findAssessmentsByIds($ids);
        $testpapersStatusNum = $this->findTestpapersStatusNum($tasks);

        array_walk($lessons, function (&$lesson, $key) use ($testpapers, $testpapersStatusNum) {
            $lesson['tasks']['assessment'] = isset($testpapers[$lesson['tasks']['assessmentId']]) ? $testpapers[$lesson['tasks']['assessmentId']] : [];
            $lesson['tasks']['assessmentStatusNum'] = isset($testpapersStatusNum[$lesson['tasks']['activityId']]) ? $testpapersStatusNum[$lesson['tasks']['activityId']] : [];
        });

        return $lessons;
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
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
    }
}
