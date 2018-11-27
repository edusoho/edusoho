<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CourseThread extends AbstractResource
{
    public function get(ApiRequest $request, $courseId, $threadId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        if (empty($course)) {
            throw new BadRequestHttpException("CourseSet#{$courseId} Not Found", null, 4041601);
        }

        $thread = $this->getCourseThreadService()->getThreadByThreadId($threadId);
        $thread = $this->addAttachments($thread);
        $thread['user'] = $this->getUserService()->getUser($thread['userId']);

        return $thread;
    }

    public function search(ApiRequest $request, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        if (empty($course)) {
            throw new BadRequestHttpException("CourseSet#{$courseId} Not Found", null, 4041601);
        }
        $type = $request->query->get('type', 'question');
        $keyword = $request->query->get('keyword');
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $conditions = array(
            'courseId' => $courseId,
            'type' => $type,
            'title' => $keyword,
        );

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
        return array(1111);
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
        if ($thread['source'] == 'app') {
            $attachments = $this->getUploadFileService()->findUseFilesByTargetTypeAndTargetIdAndType('course.thread', $thread['id'], 'attachment');
            $thread['attachments'] = array();
            foreach ($attachments as $attachment) {
                $attachment = $this->getUploadFileService()->getUseFile($attachment['id']);
                $file = $this->getUploadFileService()->getFullFile($attachment['fileId']);

                if ($file['storage'] != 'cloud') {
                    throw CommonException::ERROR_PARAMETER();
                }

//                    if ($file['targetType'] != 'attachment') {
//                        throw CommonException::ERROR_PARAMETER();
//                    }

                $download = $this->getUploadFileService()->getDownloadMetas($file['id']);

                if ($file['type'] == 'video' or $file['type'] == 'audio') {
                    $thread['attachments'][$file['type']] = $download['url'];
                } else {
                    $thread['attachments']['pictures'][] = $download['url'];
                }
            }
        }

        return $thread;
    }

    protected function getCloudFileService()
    {
        return $this->service('CloudFile:CloudFileService');
    }

    protected function getPlayerService()
    {
        return $this->service('Player:PlayerService');
    }

    protected function getTokenService()
    {
        return $this->service('User:TokenService');
    }

    protected function getMaterialLibService()
    {
        return $this->service('MaterialLib:MaterialLibService');
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
