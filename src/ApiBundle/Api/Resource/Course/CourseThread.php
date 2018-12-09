<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CourseThread extends AbstractResource
{
    public function get(ApiRequest $request, $courseId, $threadId)
    {
        $this->getCourseService()->tryTakeCourse($courseId);

        $thread = $this->getCourseThreadService()->getThreadByThreadId($threadId);

        if ($thread['source'] == 'app') {
            $attachments = $this->getUploadFileService()->findUseFilesByTargetTypeAndTargetIdAndType('course.thread', $threadId, 'attachment');
            $thread = $this->addAttachments($thread, ArrayToolkit::group($attachments, 'targetId'));
        }

        if (!empty($thread['videoId'])) {
            $file = $this->getUploadFileService()->getFullFile($thread['videoId']);
            $thread['askVideoLength'] = $file['length'];
            $thread['askVideoThumbnail'] = $file['thumbnail'];
        }
        $thread['user'] = $this->getUserService()->getUser($thread['userId']);

        return $thread;
    }

    public function search(ApiRequest $request, $courseId)
    {
        $this->getCourseService()->tryTakeCourse($courseId);

        $type = $request->query->get('type', 'question');
        $keyword = $request->query->get('keyword');
        $taskId = $request->query->get('taskId', 0);
        $conditions = array(
            'courseId' => $courseId,
            'type' => $type,
            'content' => $keyword,
        );
        if ($taskId) {
            $videoAskTime = $request->query->get('videoAskTime', 0);
            $task = $this->getTaskService()->getTask($taskId);
            $activity = $this->getActivityService()->getActivity($task['activityId'], true);
            if (!isset($activity['ext']['file'])) {
                throw new BadRequestHttpException('被提问资源不存在', null, '404');
            }
            $videoId = $activity['ext']['file']['id'];
            $conditions['videoId'] = isset($videoId) ? $videoId : 0;
            $conditions['videoAskTime_GE'] = ($videoAskTime - 60) > 0 ? $videoAskTime - 60 : 0;
            $conditions['videoAskTime_LE'] = $videoAskTime + 60;
        }

        list($offset, $limit) = $this->getOffsetAndLimit($request);

        if (!empty($keyword)) {
            $this->createSearchKeyword($keyword, $type);
        }

        $total = $this->getCourseThreadService()->countThreads($conditions);
        $threads = $this->getCourseThreadService()->searchThreads(
            $conditions,
            'createdNotStick',
            $offset,
            $limit
        );

        $attachments = $this->getUploadFileService()->searchUseFiles(array('targetType' => 'course.thread', 'targetIds' => ArrayToolkit::column($threads, 'id')));
        $attachments = ArrayToolkit::group($attachments, 'targetId');
        foreach ($threads as &$thread) {
            if ($thread['source'] == 'app') {
                $thread = $this->addAttachments($thread, $attachments);
            }
        }
        $this->getOCUtil()->multiple($threads, array('userId'));

        return $this->makePagingObject(array_values($threads), $total, $offset, $limit);
    }

    public function add(ApiRequest $request, $courseId)
    {
        $this->getCourseService()->tryTakeCourse($courseId);
        $fields = $request->request->all();
        $fields['courseId'] = $courseId;
        $fields['source'] = 'app';
        if (!ArrayToolkit::requireds($fields, array('content', 'courseId', 'type'))) {
            throw new BadRequestHttpException('缺少必填字段', null, 5000305);
        }

        if (isset($fields['taskId'])) {
            $task = $this->getTaskService()->getTask($fields['taskId']);
            $activity = $this->getActivityService()->getActivity($task['activityId'], true);
            $fields['videoId'] = ($activity['mediaType'] == 'video') ? $activity['ext']['id'] : 0;
        }

        $fields['title'] = substr($fields['content'], 0, 30);
        $thread = $this->getCourseThreadService()->createThread($fields);

        if (isset($fields['fileIds'])) {
            $this->getUploadFileService()->createUseFiles($fields['fileIds'], $thread['id'], 'course.thread', 'attachment');
        }

        return $thread;
    }

    protected function createSearchKeyword($keyword, $type)
    {
        $existKeyword = $this->getSearchKeywordService()->getSearchKeywordByNameAndType($keyword, $type);
        if ($existKeyword) {
            $this->getSearchKeywordService()->addSearchKeywordTimes($existKeyword['id']);
            $result = $this->getSearchKeywordService()->getSearchKeyword($existKeyword['id']);
        } else {
            $result = $this->getSearchKeywordService()->createSearchKeyword(array('name' => $keyword, 'type' => $type));
        }

        return $result;
    }

    protected function addAttachments($thread, $attachments)
    {
        if (isset($attachments[$thread['id']])) {
            $thread['attachments'] = array();
            foreach ($attachments[$thread['id']] as $attachment) {
                $file = isset($attachment['file']) ? $attachment['file'] : array();

                if ($file['type'] == 'video' or $file['type'] == 'audio') {
                    $thread['attachments'][$file['type']] = array(
                        'id' => $file['id'],
                        'length' => $file['length'],
                    );
                } else {
                    $thread['attachments']['pictures'][] = array(
                        'id' => $file['id'],
                        'thumbnail' => $file['thumbnail'],
                    );
                }

                if ($file['type'] == 'video') {
                    $thread['attachments'][$file['type']]['thumbnail'] = $file['thumbnail'];
                }
            }
        }

        return $thread;
    }

    /**
     * @return \Biz\Course\Service\Impl\MemberServiceImpl
     */
    protected function getMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return \Biz\Task\Service\Impl\TaskServiceImpl
     */
    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
    }

    /**
     * @return \Biz\Activity\Service\Impl\ActivityServiceImpl
     */
    protected function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }

    /**
     * @return \Biz\File\Service\Impl\UploadFileServiceImpl
     */
    protected function getUploadFileService()
    {
        return $this->service('File:UploadFileService');
    }

    /**
     * @return \Biz\SearchKeyword\Service\Impl\SearchKeywordServiceImpl
     */
    protected function getSearchKeywordService()
    {
        return $this->service('SearchKeyword:SearchKeywordService');
    }

    /**
     * @return \Biz\Course\Service\Impl\CourseServiceImpl
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return \Biz\Course\Service\Impl\MemberServiceImpl
     */
    protected function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return \Biz\Course\Service\Impl\ThreadServiceImpl
     */
    protected function getCourseThreadService()
    {
        return $this->service('Course:ThreadService');
    }

    /**
     * @return \Biz\User\Service\Impl\UserServiceImpl
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
