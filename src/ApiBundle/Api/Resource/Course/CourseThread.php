<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CourseThread extends AbstractResource
{
    public function get(ApiRequest $request, $courseId, $threadId)
    {
        $user = $this->getCurrentUser();
        $course = $this->getCourseService()->getCourse($courseId);
        $member = $this->getCourseMemberService()->getCourseMember($courseId, $user['id']);
        if (empty($course)) {
            throw new BadRequestHttpException('教学计划不存在', null, 4041601);
        }

        if (empty($member)) {
            throw new BadRequestHttpException('教学计划中没有该学员', null, 4041901);
        }

        $thread = $this->getCourseThreadService()->getThreadByThreadId($threadId);
        $thread = $this->addAttachments($thread);
        if (!empty($thread['videoId'])) {
            $file = $this->getUploadFileService()->getFullFile($thread['videoId']);
            $download = $this->getUploadFileService()->getDownloadMetas($file['id']);
            $thread['askVideoUri'] = $download['url'];
            $thread['askVideoLength'] = $file['length'];
            $thread['askVideoThumbnail'] = $file['thumbnail'];
        }
        $thread['user'] = $this->getUserService()->getUser($thread['userId']);

        return $thread;
    }

    public function search(ApiRequest $request, $courseId)
    {
        $user = $this->getCurrentUser();
        $course = $this->getCourseService()->getCourse($courseId);
        $member = $this->getCourseMemberService()->getCourseMember($courseId, $user['id']);
        if (empty($course)) {
            throw new BadRequestHttpException('教学计划不存在', null, 4041601);
        }

        if (empty($member)) {
            throw new BadRequestHttpException('教学计划中没有该学员', null, 4041901);
        }

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
            array(),
            $offset,
            $limit
        );

        foreach ($threads as &$thread) {
            $thread = $this->addAttachments($thread);
        }

        $this->getOCUtil()->multiple($threads, array('userId'));

        return $this->makePagingObject(array_values($threads), $total, $offset, $limit);
    }

    public function add(ApiRequest $request, $courseId)
    {
        $fields = $request->request->all();
        $fields['courseId'] = $courseId;
        $fields['source'] = 'app';
        $fileIds = $fields['fileIds'];
        if (!ArrayToolkit::requireds($fields, array('content', 'courseId', 'type'))) {
            throw new BadRequestHttpException('缺少必填字段', null, 5000305);
        }

        if (!$this->getCourseService()->canTakeCourse($fields['courseId'])) {
            throw new BadRequestHttpException('没有提问的权限', null, 5000512);
        }

        if ($fields['taskId']) {
            $task = $this->getTaskService()->getTask($fields['taskId']);
            $activity = $this->getActivityService()->getActivity($task['activityId'], true);
            $fields['videoId'] = ($activity['mediaType'] == 'video') ? $activity['ext']['id'] : 0;
        }

        $fields['title'] = substr($fields['content'], 0, 30);
        $thread = $this->getCourseThreadService()->createThread($fields);

        if ($fileIds) {
            $this->getUploadFileService()->createUseFiles($fileIds, $thread['id'], 'course_thread', 'attachment');
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
            $result = $this->getSearchKeywordService()->createSearchKeyword(array('name' => $keyword));
        }

        return $result;
    }

    protected function addAttachments($thread)
    {
        $attachments = $this->getUploadFileService()->findUseFilesByTargetTypeAndTargetIdAndType('course.thread', $thread['id'], 'attachment');
        if ($thread['source'] == 'app' && !empty($attachments)) {
            $thread['attachments'] = array();
            foreach ($attachments as $attachment) {
                $attachment = $this->getUploadFileService()->getUseFile($attachment['id']);
                $file = $this->getUploadFileService()->getFullFile($attachment['fileId']);

                if ($file['storage'] != 'cloud') {
                    throw CommonException::ERROR_PARAMETER();
                }

//                if ($file['targetType'] != 'attachment') {
//                    throw CommonException::ERROR_PARAMETER();
//                }

                $download = $this->getUploadFileService()->getDownloadMetas($file['id']);

                if ($file['type'] == 'video' or $file['type'] == 'audio') {
                    $thread['attachments'][$file['type']] = array(
                        'uri' => $download['url'],
                        'length' => $file['length'],
                    );
                } else {
                    $thread['attachments']['pictures'][] = $download['url'];
                }

                if ($file['type'] == 'video') {
                    $thread['attachments'][$file['type']]['thumbnail'] = ($file['thumbnail']) ? $file['thumbnail'] : '';
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
