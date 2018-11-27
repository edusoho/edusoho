<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;

class CourseThreadPost extends AbstractResource
{
    public function search(ApiRequest $request, $courseId, $threadId)
    {
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

        $this->getOCUtil()->multiple($posts, array('userId'));
        $posts = $this->addAttachments($posts);

        return $this->makePagingObject(array_values($posts), $total, $offset, $limit);
    }

    protected function addAttachments($posts)
    {
        foreach ($posts as &$post) {
            if ($post['source'] == 'app') {
                $attachments = $this->getUploadFileService()->findUseFilesByTargetTypeAndTargetIdAndType('course.thread.post', $post['id'], 'attachment');
                $post['attachments'] = array();
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
                        $post['attachments'][$file['type']] = array(
                            'uri' => $download['url'],
                            'length' => $file['length'],
                        );
                    } else {
                        $post['attachments']['pictures'][] = $download['url'];
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
