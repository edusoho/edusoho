<?php

namespace AppBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;

class LatestFinishedLearnsDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取最近完成学习列表.
     *
     * 可传入的参数：
     *   count    必需 课程数量，取值不能超过100
     *
     * @param array $arguments 参数
     *
     * @return array 课程列表
     */
    public function getData(array $arguments)
    {
        if (empty($arguments['count'])) {
            $arguments['count'] = 5;
        }

        $conditions = array(
            'userId' => $this->getCurrentUser()->id,
            'status' => 'finish',
        );
        $learns = $this->getTaskResultService()->searchTaskResults($conditions, array('createdTime' => 'DESC'), 0, $arguments['count']);

        $userIds = ArrayToolkit::column($learns, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $taskIds = ArrayToolkit::column($learns, 'id');
        $tasks = $this->getTaskService()->findTasksByIds($taskIds);
        $tasks = ArrayToolkit::index($tasks, 'id');

        foreach ($learns as $key => $learn) {
            if ($learn['userId'] == $users[$learn['userId']]['id']) {
                $learns[$key]['user'] = $users[$learn['userId']];
            }

            if (!empty($tasks[$learn['courseTaskId']]['id']) && $learn['courseTaskId'] == $tasks[$learn['courseTaskId']]['id']) {
                $learns[$key]['lesson'] = $tasks[$learn['courseTaskId']];
            }
        }

        return $learns;
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->getBiz()->service('User:UserService');
    }

    protected function getTaskResultService()
    {
        return $this->getServiceKernel()->getBiz()->service('Task:TaskResultService');
    }

    protected function getTaskService()
    {
        return $this->getServiceKernel()->getBiz()->service('Task:TaskService');
    }
}
