<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Util\AssetHelper;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\File\UploadFileException;

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

        if ($thread['source'] == 'web') {
            $thread['content'] = $this->filterHtml($thread['content']);
        }

        if (!empty($thread['videoId'])) {
            $file = $this->getUploadFileService()->getFile($thread['videoId']);
            $thread['askVideoLength'] = $file['length'];
        }
        $thread['user'] = $this->getUserService()->getUser($thread['userId']);
        $this->getCourseThreadService()->hitThread($courseId, $threadId);

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
            'title' => $keyword,
        );
        if ($taskId) {
            $videoAskTime = $request->query->get('videoAskTime', 0);
            $task = $this->getTaskService()->getTask($taskId);
            $activity = $this->getActivityService()->getActivity($task['activityId'], true);
            if (!isset($activity['ext']['file'])) {
                throw UploadFileException::NOTFOUND_FILE();
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

        $attachments = $this->getUploadFileService()->searchUseFiles(array('targetType' => 'course.thread', 'targetIds' => ArrayToolkit::column($threads, 'id')), true, array('id' => 'ASC'));
        $attachments = ArrayToolkit::group($attachments, 'targetId');
        foreach ($threads as &$thread) {
            if ($thread['source'] == 'app') {
                $thread = $this->addAttachments($thread, $attachments);
            }

            $thread['content'] = !empty($thread['content']) ? $this->filterHtml($thread['content']) : $thread['content'];
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
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        if (isset($fields['taskId'])) {
            $task = $this->getTaskService()->getTask($fields['taskId']);
            $activity = $this->getActivityService()->getActivity($task['activityId'], true);
            $fields['videoId'] = ($activity['mediaType'] == 'video') ? $activity['ext']['file']['id'] : 0;
        }

        $fields['title'] = substr($fields['content'], 0, 100);
        if (empty($fields['title'])) {
            $fields['questionType'] = $this->getQuestionType($fields['fileIds']);
        }
        $thread = $this->getCourseThreadService()->createThread($fields);

        if (isset($fields['fileIds'])) {
            $this->getUploadFileService()->createUseFiles($fields['fileIds'], $thread['id'], 'course.thread', 'attachment');
        }

        return $thread;
    }

    protected function filterHtml($text)
    {
        preg_match_all('/\<img.*?src\s*=\s*[\'\"](.*?)[\'\"]/i', $text, $matches);
        if (empty($matches)) {
            return $text;
        }

        foreach ($matches[1] as $url) {
            $text = str_replace($url, AssetHelper::uriForPath($url), $text);
        }

        return $text;
    }

    protected function getQuestionType($fileIds)
    {
        $files = $this->getUploadFileService()->findFilesByIds($fileIds, false);
        $types = ArrayToolkit::column($files, 'type');

        switch ($types) {
            case in_array('video', $types):
                return 'video';
            case in_array('image', $types):
                return 'image';
            case in_array('audio', $types):
                return 'audio';
            default:
                return 'content';
        }
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
                        'thumbnail' => isset($file['thumbnail']) ? $file['thumbnail'] : '',
                    );
                }

                if ($file['type'] == 'video') {
                    $thread['attachments'][$file['type']]['thumbnail'] = isset($file['thumbnail']) ? $file['thumbnail'] : '';
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
