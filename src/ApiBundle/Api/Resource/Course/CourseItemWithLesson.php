<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\ExerciseActivityService;
use Biz\Activity\Service\HomeworkActivityService;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Util\EdusohoLiveClient;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;

class CourseItemWithLesson extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (!$course) {
            throw CourseException::NOTFOUND_COURSE();
        }
        $courseItems = [];
        $userId = $this->getCurrentUser()->getId();
        $member = $this->getCourseMemberService()->getCourseMember($courseId, $userId);
        if ($course['taskDisplay'] || $member) {
            $courseItems = $this->getCourseService()->findCourseItems($courseId);
        }

        $items = $this->convertToLeadingItems(
            $courseItems,
            $course,
            $request->getHttpRequest()->isSecure(),
            $request->query->get('fetchSubtitlesUrls', 0),
            $request->query->get('onlyPublished', 0),
            $request->query->get('showOptionalNum', 1)
        );
        $needReplayStatus = $request->query->get('needReplayDownloadStatus', 0);

        if ($needReplayStatus) {
            $liveReplays = $this->getLiveReplays($courseItems);
        }

        foreach ($items as &$item) {
            if (!empty($item['tasks'])) {
                foreach ($item['tasks'] as &$task) {
                    if ('live' === $task['type'] && !empty($activityLive = $task['activity']['ext'])) {
                        if ($needReplayStatus) {
                            $task['liveId'] = $activityLive['liveId'];
                            $task['replayDownloadStatus'] = !empty($liveReplays[$activityLive['liveId']]) ? ('finished' === $liveReplays[$activityLive['liveId']]['status'] ? 'finished' : 'un_finished') : 'none';
                        }
                        $task['liveStatus'] = $liveStatus = $activityLive['progressStatus'];
                        $currentTime = time();
                        if ('created' === $liveStatus && $currentTime > $task['activity']['startTime']) {
                            $task['liveStatus'] = EdusohoLiveClient::LIVE_STATUS_LIVING;
                        }
                        if ('created' === $liveStatus && $currentTime > $task['activity']['endTime']) {
                            $task['liveStatus'] = EdusohoLiveClient::LIVE_STATUS_CLOSED;
                        }
                    }
                    if ('homework' === $task['type'] && !empty($task['activity'])) {
                        $homeworkActivity = $this->getHomeworkActivityService()->get($task['activity']['mediaId']);
                        $task['activity']['mediaId'] = $homeworkActivity['assessmentId'];
                    }
                    if ('testpaper' === $task['type'] && !empty($task['activity']['ext'])) {
                        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($task['activity']['mediaId']);
                        $scene = $this->getAnswerSceneService()->get($testpaperActivity['answerSceneId']);
                        if (!empty($scene)) {
                            $task['activity']['ext']['doTimes'] = $scene['do_times'];
                            $task['activity']['ext']['redoInterval'] = $scene['redo_interval'];
                            $task['activity']['ext']['limitedTime'] = $scene['limited_time'];
                        }
                    }
                }
            }
        }

        $request->query->has('format') ? $format = $request->query->get('format') : $format = 0;

        if ($format) {
            $filter = new CourseItemWithLessonFilter();
            $filter->filters($items);
            $items = $this->convertToTree($items);
        }

        return $items;
    }

    protected function getLiveReplays($courseItems)
    {
        $liveIds = [];
        foreach ($courseItems as $courseItem) {
            if (!empty($courseItem['tasks'])) {
                foreach ($courseItem['tasks'] as $courseItemTask) {
                    if ('live' === $courseItemTask['type'] && !empty($courseItemTask['activity']['ext'])) {
                        $liveIds[] = $courseItemTask['activity']['ext']['liveId'];
                    }
                    if ('replay' === $courseItemTask['type'] && !empty($courseItemTask['activity']['ext'])) {
                        $activity = $this->getActivityService()->getActivity($courseItemTask['activity']['ext']['origin_lesson_id'], true);
                        $liveIds[] = $activity['ext']['liveId'];
                    }
                }
            }
        }

        $client = new EdusohoLiveClient();
        $replayInfos = [];
        foreach (array_chunk($liveIds, 100) as $liveIdsChunk) {
            $replayInfos = array_merge($replayInfos, $client->batchGetReplayInfosForSelfLive($liveIdsChunk));
        }

        return ArrayToolkit::index($replayInfos, 'liveRoomId');
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
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    protected function getSubtitleService()
    {
        return $this->service('Subtitle:SubtitleService');
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
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->service('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return ExerciseActivityService
     */
    protected function getExerciseActivityService()
    {
        return $this->service('Activity:ExerciseActivityService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return AnswerService
     */
    protected function getAnswerService()
    {
        return $this->service('ItemBank:Answer:AnswerService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }
}
