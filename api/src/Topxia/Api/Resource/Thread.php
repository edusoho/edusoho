<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Thread extends BaseResource
{
	public function create(Application $app, Request $request, $id)
    {
    	try{
            $data = $request->request->all();
            $data['targetType'] = $target['type'];
            $data['targetId'] = $target['id'];

            $thread = $this->getThreadService()->createThread($data);
            if (!empty($thread)) {
            	return array("threadId" => $thread['id']);
            }
        } catch (\Exception $e){
            return $this->error('500', '发帖错误');
        }

        return $this->error('500', '发帖错误');
    }

    public function filter(&$res)
    {
        return $res;
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Thread.ThreadService');
    }
}