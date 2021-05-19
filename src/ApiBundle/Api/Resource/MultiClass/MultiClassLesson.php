<?php


namespace ApiBundle\Api\Resource\MultiClass;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\ThreadService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;

class MultiClassLesson extends AbstractResource
{
    public function search(ApiRequest $request, $multiClassId)
    {
        $multiClass = $this->getMultiClassService()->getMultiClass($multiClassId);

        $course = $this->getCourseService()->getCourse($multiClass['courseId']);
        if (empty($course)) {
            throw CourseException::NOTFOUND_COURSE();
        }

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $conditions = [
            'courseId' => $course['id'],
            'titleLike' => $request->query->get('titleLike', ''),
            'types' => $request->query->get('types', [])
        ];
        $items = $this->getCourseService()->searchMultiClassCourseItems($conditions, ['startTime' => $request->query->get('sort', 'ASC')], $offset, $limit);
        $total = $this->getTaskService()->countTasks($conditions);

        $items = $this->getNecessaryItems($items, $course, $request);

        return $this->makePagingObject($items, $total, $offset, $limit);
    }

    protected function getNecessaryItems($items, $course, $request)
    {
        $items = $this->convertToLeadingItems($items, $course, $request->getHttpRequest()->isSecure(), 0);
        $items = $this->convertToTree($items);
        $necessaryItems = [];
        foreach ($items as $item){
            $units = $item['children'];
            foreach ($units as $unit){
                $lessons = $unit['children'];
                foreach ($lessons as &$lesson){
                    if ($lesson['isExist']){
                        $lesson['chapterTitle'] = $item['title'];
                        $lesson['unitTitle'] = $unit['title'];
                        foreach ($lesson['tasks'] as $key => $task){
                            if ($task['mode'] === 'lesson' && $task['isLesson']){
                                $lesson['tasks'] = $task;
                            }
                        }
                        $teacher = $this->getCourseMemberService()->getMultiClassMembers($lesson['tasks']['courseId'], $lesson['tasks']['multiClassId'], 'teacher');
                        $lesson['teacher'] = $teacher ? $teacher[0] : [];
                        $lesson['assistant'] = $this->getCourseMemberService()->getMultiClassMembers($lesson['tasks']['courseId'], $lesson['tasks']['multiClassId'],'assistant');
                        $lesson['questions'] = $this->getThreadService()->countThreads(['courseId' => $lesson['tasks']['courseId'], 'type' => 'question']);
                        $lesson['studyStudentNum'] = count(array_unique(array_column($this->getTaskResultService()->findTaskresultsByTaskId($lesson['tasks']['id']), 'userId')));
                        $lesson['totalStudentNum'] = $this->getCourseMemberService()->getCourseStudentCount($lesson['tasks']['courseId']);
                        $necessaryItems[] = $lesson;
                    }
                }
            }
        }

        return $necessaryItems;
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
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->service('Course:ThreadService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->service('Task:TaskResultService');
    }
}