<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Util\AssetHelper;
use Biz\Common\CommonException;
use AppBundle\Common\ArrayToolkit;

class CourseThreadPost extends AbstractResource
{
    public function get(ApiRequest $request, $courseId, $postId)
    {
        $this->getCourseService()->tryTakeCourse($courseId);

        $post = $this->getCourseThreadService()->getPost($courseId, $postId);
        $posts = array($post);
        if (!empty($posts)) {
            $posts = $this->readPosts($courseId, $posts);
        }
        $posts = $this->addAttachments($posts);
        $post = array_shift($posts);
        $this->getOCUtil()->single($post, array('userId'));

        return $post;
    }

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
            $request->query->get('sort', array()),
            $offset,
            $limit
        );

        if (!empty($posts)) {
            $posts = $this->readPosts($courseId, $posts);
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
        $params['source'] = 'app';
        $fileIds = isset($params['fileIds']) ? $params['fileIds'] : array();

        if (empty($params['content'])) {
            $params['postType'] = $this->getPostType($params['fileIds']);
        }
        unset($params['fileIds']);
        $post = $this->getCourseThreadService()->createPost($params);
        if (isset($fileIds)) {
            $this->getUploadFileService()->createUseFiles($fileIds, $post['id'], 'course.thread.post', 'attachment');
        }
        $this->getOCUtil()->single($post, array('userId'));

        return $post;
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

    protected function readPosts($courseId, $posts)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $userId = $this->getCurrentUser()->getId();
        foreach ($posts as &$post) {
            if (in_array($userId, $course['teacherIds']) && $post['userId'] != $userId) {
                $post = $this->getCourseThreadService()->readPost($post['id']);
            }

            if (!in_array($userId, $course['teacherIds']) && in_array($post['userId'], $course['teacherIds'])) {
                $post = $this->getCourseThreadService()->readPost($post['id']);
            }

            $post['content'] = !empty($post['content']) ? $this->filterHtml($post['content']) : $post['content'];
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
                            'thumbnail' => isset($file['thumbnail']) ? $file['thumbnail'] : '',
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

    protected function getPostType($fileIds)
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
