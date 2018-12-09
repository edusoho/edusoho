<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use AppBundle\Common\ArrayToolkit;

class CourseThreadPost extends AbstractResource
{
    public function search(ApiRequest $request, $courseId, $threadId)
    {
        $this->getCourseService()->tryTakeCourse($courseId);

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $afterTime = $request->query->get('afterTime');
        $conditions = array(
            'threadId' => $threadId,
            'createdTime_GE' => $afterTime,
        );

        $total = $this->getCourseThreadService()->getThreadPostCountByThreadId($threadId);
        $posts = $this->getCourseThreadService()->searchThreadPosts(
            $conditions,
            array(),
            $offset,
            $limit
        );

        if (!empty($posts)) {
            $posts = $this->readPosts($courseId, $threadId, $posts);
        }
        $posts = $this->addAttachments($posts);

        $this->getOCUtil()->multiple($posts, array('userId'));

        return $this->makePagingObject(array_values($posts), $total, $offset, $limit);
    }

    public function add(ApiRequest $apiRequest, $courseId, $threadId)
    {
        $this->getCourseService()->tryTakeCourse($courseId);
        $thread = $this->getCourseThreadService()->getThread($courseId, $threadId);

        $params = $apiRequest->request->all();
        $params['content'] = isset($params['content']) ? $params['content'] : null;
        $params['threadId'] = $threadId;
        $params['courseId'] = $courseId;

        $post = $this->getCourseThreadService()->createPost($params);
        if (isset($params['fileIds'])) {
            $this->getUploadFileService()->createUseFiles($params['fileIds'], $post['id'], 'course.thread.post', 'attachment');
        }

        return $post;
    }

    protected function readPosts($courseId, $threadId, $posts)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $thread = $this->getCourseThreadService()->getThread($courseId, $threadId);
        $userId = $this->getCurrentUser()->getId();
        foreach ($posts as &$post) {
            if (in_array($userId, $course['teacherIds']) && $post['userId'] != $userId) {
                $post = $this->getCourseThreadService()->readPost($post['id']);
            }

            if (!in_array($userId, $course['teacherIds']) && in_array($post['userId'], $course['teacherIds'])) {
                $post = $this->getCourseThreadService()->readPost($post['id']);
            }
        }

        return $posts;
    }

    protected function addAttachments($posts)
    {
        $attachments = $this->getUploadFileService()->searchUseFiles(array('targetType' => 'course.thread.post', 'targetIds' => ArrayToolkit::column($posts, 'id')));
        $attachments = ArrayToolkit::group($attachments, 'targetId');
        foreach ($posts as &$post) {
            if ($post['source'] == 'app' && isset($attachments[$post['id']])) {
                $post['attachments'] = array();
                foreach ($attachments[$post['id']] as $attachment) {
                    $file = isset($attachment['file']) ? $attachment['file'] : array();

                    if ($file['storage'] != 'cloud') {
                        throw CommonException::ERROR_PARAMETER();
                    }

                    if ($file['targetType'] != 'attachment') {
                        throw CommonException::ERROR_PARAMETER();
                    }

                    if ($file['type'] == 'video' or $file['type'] == 'audio') {
                        $post['attachments'][$file['type']] = array(
                            'id' => $file['id'],
                            'length' => $file['length'],
                        );
                    } else {
                        $post['attachments']['pictures'][] = array(
                            'id' => $file['id'],
                            'thumbnail' => $file['thumbnail'],
                        );
                    }

                    if ($file['type'] == 'video') {
                        $post['attachments'][$file['type']]['thumbnail'] = ($file['thumbnail']) ? $file['thumbnail'] : '';
                    }
                }
            }
        }

        return $posts;
    }

    /**
     * @return \Biz\File\Service\Impl\UploadFileServiceImpl
     */
    protected function getUploadFileService()
    {
        return $this->service('File:UploadFileService');
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
}
