<?php

namespace Biz\Xapi\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\AccessDeniedException;
use AppBundle\Common\Exception\InvalidArgumentException;
use Biz\BaseService;
use Biz\Task\Service\TaskService;
use Biz\Xapi\Dao\ActivityWatchLogDao;
use Biz\Xapi\Dao\StatementDao;
use Biz\Xapi\Service\XapiService;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;

class XapiServiceImpl extends BaseService implements XapiService
{
    public function createStatement($statement)
    {
        if (empty($this->biz['user'])) {
            throw new AccessDeniedException('user is not login.');
        }

        if (!ArrayToolkit::requireds($statement, array('data'))) {
            throw new InvalidArgumentException('args is invalid, miss required fields.');
        }

        if (!ArrayToolkit::requireds($statement['data'], array('actor', 'verb', 'object'))) {
            throw new InvalidArgumentException('statement is invalid, miss required fields.');
        }

        $statement['version'] = $this->biz['xapi.options']['version'];
        $statement['user_id'] = $this->biz['user']['id'];
        $statement['uuid'] = $this->generateUUID();

        return $this->getStatementDao()->create($statement);
    }

    protected function generateUUID()
    {
        mt_srand((float) microtime() * 10000);
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);
        $uuid = ''.substr($charid, 0, 8).$hyphen.substr($charid, 8, 4).$hyphen.substr($charid, 12, 4).$hyphen.substr($charid, 16, 4).$hyphen.substr($charid, 20, 12);

        return $uuid;
    }

    public function updateStatementsPushedByStatementIds($statementIds)
    {
        $batchUpdateHelper = new BatchUpdateHelper($this->getStatementDao());
        foreach ($statementIds as $statementId) {
            $batchUpdateHelper->add('id', $statementId, array(
                'status' => 'pushed',
                'push_time' => time(),
            ));
        }
        $batchUpdateHelper->flush();
    }

    public function updateStatementsPushingByStatementIds($statementIds)
    {
        $batchUpdateHelper = new BatchUpdateHelper($this->getStatementDao());
        foreach ($statementIds as $statementId) {
            $batchUpdateHelper->add('id', $statementId, array(
                'status' => 'pushing',
            ));
        }
        $batchUpdateHelper->flush();
    }

    public function searchStatements($conditions, $orders, $start, $limit)
    {
        return $this->getStatementDao()->search($conditions, $orders, $start, $limit);
    }

    public function getWatchLog($id)
    {
        return $this->getActivityWatchLogDao()->get($id);
    }

    public function getLatestWatchLogByUserIdAndActivityId($userId, $activityId, $isPush = 0)
    {
        return $this->getActivityWatchLogDao()->getLatestWatchLogByUserIdAndActivityId($userId, $activityId, $isPush);
    }

    public function createWatchLog($watchLog)
    {
        return $this->getActivityWatchLogDao()->create($watchLog);
    }

    public function updateWatchLog($id, $watchLog)
    {
        return $this->getActivityWatchLogDao()->update($id, $watchLog);
    }

    public function watchTask($taskId, $watchTime)
    {
        $user = $this->getCurrentUser();
        $task = $this->getTaskService()->tryTakeTask($taskId);
        $watchLog = $this->getLatestWatchLogByUserIdAndActivityId($user['id'], $task['activityId']);
        if (empty($watchLog) || $watchLog['created_time'] < time() - 30 * 60) {
            $log = array(
                'activity_id' => $task['activityId'],
                'course_id' => $task['courseId'],
                'task_id' => $task['id'],
                'watched_time' => $watchTime,
            );

            $this->createWatchLog($log);
        } else {
            $this->getActivityWatchLogDao()->wave(array($watchLog['id']), array('watched_time' => $watchTime));
        }
    }

    /**
     * @return StatementDao
     */
    protected function getStatementDao()
    {
        return $this->biz->dao('Xapi:StatementDao');
    }

    /**
     * @return ActivityWatchLogDao
     */
    protected function getActivityWatchLogDao()
    {
        return $this->biz->dao('Xapi:ActivityWatchLogDao');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }
}
