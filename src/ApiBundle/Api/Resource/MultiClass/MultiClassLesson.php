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
            'types' => $request->query->get('types', []),
        ];
        $items = $this->getCourseService()->searchMultiClassCourseItems($conditions, ['startTime' => $request->query->get('sort', 'ASC')], $offset, $limit);
        $total = $this->getTaskService()->countTasks($conditions);

        $items = $this->getNecessaryItems($items, $multiClass['id'], $course, $request);

        return $this->makePagingObject($items, $total, $offset, $limit);
    }

    protected function getNecessaryItems($items, $multiClassId, $course, $request)
    {
        $items = $this->convertToLeadingItems($items, $course, $request->getHttpRequest()->isSecure(), 0);
        $items = $this->convertToTree($items);
        $teacher = $this->getCourseMemberService()->getMultiClassMembers($course['id'], $multiClassId, 'teacher');
        $assistants = $this->getCourseMemberService()->getMultiClassMembers($course['id'], $multiClassId, 'assistant');
        $questionNum = $this->getThreadService()->countThreads(['courseId' => $course['id'], 'type' => 'question']);
        $totalStudentNum = $this->getCourseMemberService()->getCourseStudentCount($course['id']);
        $necessaryItems = [];
        foreach ($items as $item) {
            $units = $item['children'];
            foreach ($units as $unit) {
                $lessons = $unit['children'];
                foreach ($lessons as &$lesson) {
                    if ($lesson['isExist']) {
                        $lesson['chapterTitle'] = $item['title'];
                        $lesson['unitTitle'] = $unit['title'];
                        $lesson['teacher'] = $teacher ? $teacher[0] : [];
                        $lesson['assistant'] = $assistants;
                        $lesson['questionNum'] = $questionNum;
                        $lesson['totalStudentNum'] = $totalStudentNum;
                        array_multisort(array_column($lesson['tasks'], 'seq'), SORT_ASC, $lesson['tasks']);
                        foreach ($lesson['tasks'] as $key => $task) {
                            $lesson['studyStudentNum'] = $this->getTaskResultService()->countUserNumByCourseTaskId(['courseTaskId' => $task['id']]);
                            if (isset($task['type']) && 'live' === $task['type']) {
                                if (time() < $task['activity']['startTime']) {
                                    $task['activity']['ext']['progressStatus'] = 'created';
                                } elseif (time() >= $task['activity']['startTime'] && time() < $task['activity']['endTime']) {
                                    $task['activity']['ext']['progressStatus'] = 'start';
                                } elseif (time() > $task['activity']['endTime']) {
                                    $task['activity']['ext']['progressStatus'] = 'closed';
                                }
                            }
                            $lesson['tasks'] = $task;
                            $necessaryItems[] = $lesson;
                        }
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
