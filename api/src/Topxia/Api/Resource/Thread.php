<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Thread extends BaseResource
{
    /**
     * @todo  有问题，要重写。
     */
    public function create(Application $app, Request $request)
    {
        try {
            $data = $request->request->all();
            if (!isset($data['type'])) {
                $data['type'] = 'question';
            }

            $targetType = $data['targetType'];
            $targetId   = $data['targetId'];

            if (empty($targetId) || empty($targetType)) {
                return $this->error('500', '创建问答失败，缺失数据');
            }
            if ('lesson' == $targetType) {
                $lesson = $this->getCourseService()->getLesson($targetId);
                if (empty($lesson)) {
                    return $this->error('500', '课时不存在');
                }
                $data['courseId'] = $lesson['courseId'];
                $data['lessonId'] = $targetId;
            } else {
                $data['courseId'] = $targetId;
            }

            unset($data['targetId']);
            unset($data['targetType']);

            $thread = $this->getThreadService()->createThread($data);
            if (!empty($thread)) {
                return array('threadId' => $thread['id']);
            }
        } catch (\Exception $e) {
            return $this->error('500', $e->getMessage());
        }

        return $this->error('500', '发帖错误');
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course:ThreadService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    public function filter($res)
    {
        $res['updateTime']  = date('c', isset($res['updatedTime']) ? $res['updatedTime'] : $res['updateTime']);
        $res['createdTime'] = date('c', $res['createdTime']);
        $res['threadId']    = $res['id'];

        unset($res['id']);
        unset($res['relationId']);
        unset($res['categoryId']);
        unset($res['ats']);
        unset($res['nice']);
        unset($res['sticky']);
        unset($res['solved']);
        unset($res['lastPostUserId']);
        unset($res['lastPostTime']);
        unset($res['location']);
        unset($res['memberNum']);
        unset($res['maxUsers']);
        unset($res['actvityPicture']);
        unset($res['status']);
        unset($res['startTime']);
        unset($res['endTime']);
        unset($res['body']);

        return $res;
    }
}
