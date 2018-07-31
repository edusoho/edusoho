<?php

namespace ApiBundle\Api\Resource\Testpaper;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Activity\Service\ActivityService;
use Biz\Task\Service\TaskService;
use Biz\Testpaper\Service\TestpaperService;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Testpaper extends AbstractResource
{
    public function get(ApiRequest $request, $testId)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw new AccessDeniedHttpException('用户未登录，不能查看试卷');
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($testId);

        if (empty($testpaper)) {
            throw new NotFoundHttpException('试卷已删除，请联系管理员。!');
        }

        $items = $this->getTestpaperService()->showTestpaperItems($testId);
        $testpaper['metas']['question_type_seq'] = array_keys($items);
        $results = array(
            'testpaper' => $testpaper,
            'items' => $this->filterTestpaperItems($items),
        );

        $targetType = $request->query->get('targetType');
        $targetId = $request->query->get('targetId');
        if (empty($targetType) || empty($targetId)) {
            throw new AccessDeniedHttpException('没有设定试卷所属范围，不能查看试卷！');
        }
        $method = 'handle'.$targetType;
        if (!method_exists($this, $method)) {
            throw new \BadMethodCallException(sprintf('Unknown property "%s" on Testpaper "%s".', $targetType, get_class($this)));
        }
        $this->$method($testId, $targetId, $results);

        return $results;
    }

    protected function handleTask($testId, $taskId, &$results)
    {
        $task = $this->getTaskService()->tryTakeTask($taskId);
        if (empty($task)) {
            throw new NotFoundHttpException('任务不存在');
        }
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        if ('testpaper' != $activity['mediaType'] || $activity['ext']['mediaId'] != $testId) {
            throw new AccessDeniedHttpException('试卷不属于当前任务！');
        }

        $task['activity'] = $activity;
        $results['task'] = $task;
    }

    private function filterTestpaperItems($items)
    {
        $itemArray = array();

        foreach ($items as $questionType => $item) {
            $itemArray[$questionType] = count($item);
        }

        return $itemArray;
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->getBiz()->service('Testpaper:TestpaperService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }
}
