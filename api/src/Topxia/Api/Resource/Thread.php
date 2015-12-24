<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Thread extends BaseResource
{
	public function create(Application $app, Request $request)
    {
    	try{
            $data = $request->request->all();
            if (!isset($data["type"])) {
                $data['type'] = "question";
            }

            $targetType = $data['targetType'];
            $targetId = $data['targetId'];

            if (empty($targetId) || empty($targetType)) {
                return $this->error('500', "创建问答失败，缺失数据");
            }
            if ("lesson" == $targetType) {
                $lesson = $this->getCourseService()->getLesson($targetId);
                if (empty($lesson)) {
                    return $this->error('500', "课时不存在");
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
            	return array("threadId" => $thread['id']);
            }
        } catch (\Exception $e){
            return $this->error('500', $e->getMessage());
        }

        return $this->error('500', '发帖错误');
    }

    public function filter(&$res)
    {
        return $res;
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}