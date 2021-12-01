<?php

use Biz\Activity\Dao\ActivityDao;
use Biz\Activity\Dao\LiveActivityDao;
use Biz\Course\Dao\CourseDao;
use Biz\Live\Dao\LiveStatisticsDao;
use Biz\LiveStatistics\Dao\LiveMemberStatisticsDao;
use Biz\Marker\Dao\MarkerDao;
use Biz\Util\EdusohoLiveClient;
use Symfony\Component\Filesystem\Filesystem;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;
use Topxia\Service\Common\ServiceKernel;

class EduSohoUpgrade extends AbstractUpdater
{
    private $perPageCount = 20;

    public function __construct($biz)
    {
        parent::__construct($biz);
    }

    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $result = $this->updateScheme((int)$index);

            $this->getConnection()->commit();

            if (!empty($result)) {
                return $result;
            } else {
                $this->logger('info', '执行升级脚本结束');
            }
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            $this->logger('error', $e->getTraceAsString());
            throw $e;
        }

        try {
            $dir = realpath($this->biz['kernel.root_dir'] . '/../web/install');
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
            $this->logger('error', $e->getTraceAsString());
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set('crontab_next_executed_time', time());
    }

    private function updateScheme($index)
    {
        $definedFuncNames = array(
            'processActivityLiveTime',
            'processActivityLiveProgressStatus',
            'processLiveStatisticsMemberData',
            'processLiveCloudStatisticData',
            'registerCallbackUrl'
        );

        $funcNames = array();
        foreach ($definedFuncNames as $key => $funcName) {
            $funcNames[$key + 1] = $funcName;
        }

        if (0 == $index) {
            $this->logger('info', '开始执行升级脚本');

            return array(
                'index' => $this->generateIndex(1, 1),
                'message' => '升级数据...',
                'progress' => 0,
            );
        }

        list($step, $page) = $this->getStepAndPage($index);
        $method = $funcNames[$step];
        $page = $this->$method($page);

        if (1 == $page) {
            ++$step;
        }

        if ($step <= count($funcNames)) {
            return array(
                'index' => $this->generateIndex($step, $page),
                'message' => '升级数据...',
                'progress' => 0,
            );
        }
    }

    public function processActivityLiveTime()
    {
        $this->getConnection()->exec("update activity_live a  join activity b on a.id = b.mediaId set a.liveEndTime = b.endTime,a.liveStartTime=b.startTime where b.mediaType = 'live';");
        $this->logger('info', '修改直播时间数据');
        return 1;
    }

    public function processLiveStatisticsMemberData($page)
    {
        $lives = $this->getLiveStatisticsDao()->search(['type'=>'visitor'],['createdTime'=>'ASC'],($page-1) * 50, 50);
        if(empty($lives)){
            return 1;
        }
        $liveIds = \AppBundle\Common\ArrayToolkit::column($lives, 'liveId');
        $liveActivities = $this->getLiveActivityDao()->search(['liveIds' => $liveIds], [], 0, count($liveIds));
        $activityIds = \AppBundle\Common\ArrayToolkit::column($liveActivities, 'id');
        $liveActivities = \AppBundle\Common\ArrayToolkit::index($liveActivities, 'liveId');
        $activities = $this->getActivityDao()->search(['mediaIds' => $activityIds, 'mediaType'=> 'live', 'copyId'=>0], [], 0, count($activityIds));
        $activities = \AppBundle\Common\ArrayToolkit::index($activities, 'mediaId');
        $count = $this->getUserDao()->count([]);

        foreach ($lives as $live){
            $liveCount = $this->getLiveStatisticsDao()->count(['liveId'=>$live['liveId']]);
            if($liveCount == 0 ||empty($liveActivities[$live['liveId']]) || empty($activities[$liveActivities[$live['liveId']]['id']])){
                continue;
            }
            $liveMembers = $this->getLiveMemberStatisticsDao()->search(['liveId'=>$live['liveId']],[],0,PHP_INT_MAX,['userId']);
            $userIds = \AppBundle\Common\ArrayToolkit::column($liveMembers, 'userId');
            $activity = $activities[$liveActivities[$live['liveId']]['id']];
            $create = [];
            if(empty($live['data']['detail'])){
                continue;
            }
            foreach ($live['data']['detail'] as $user){
                $userId = $user['userId'];
                if( $userId > $count){
                    $baseUser = $this->getUserDao()->getByNickname($user['nickname']);
                    $userId = $baseUser['id'];
                }
                if(!empty($create[$live['liveId'].'-'.$userId]) || empty($user['firstJoin']) || in_array($userId, $userIds)){
                    continue;
                }
                $create[$live['liveId'].'-'.$userId] = [
                    'liveId' => $live['liveId'],
                    'userId' => $userId,
                    'firstEnterTime' => $user['firstJoin'],
                    'watchDuration'=> empty($user['learnTime']) || $user['learnTime']<0 ? 0:$user['learnTime'],
                    'checkinNum' => 0,
                    'requestTime' => time(),
                    'chatNum' => 0,
                    'answerNum' => 0,
                ];
            }
            if(!empty($create)){
                $this->getLiveMemberStatisticsDao()->batchCreate(array_values($create));
            }
        }
        $this->logger('info', '修改LiveMemberStatistics数据');
        return $page+1;
    }

    public function processLiveCloudStatisticData($page)
    {
        $liveActivities = $this->getLiveActivityDao()->search([],['id'=>'ASC'],($page-1) * 50, 50);
        if(empty($liveActivities)){
            return 1;
        }
        $liveActivities = \AppBundle\Common\ArrayToolkit::index($liveActivities, 'liveId');
        $update=[];
        foreach ($liveActivities as $liveActivity){
            if($liveActivity['progressStatus'] != 'closed' ){
                continue;
            }
            $time = $this->getLiveMemberStatisticsDao()->sumWatchDurationByLiveId($liveActivity['liveId']);
            $count = $this->getLiveMemberStatisticsDao()->count(['liveId'=>$liveActivity['liveId']]);
            $update[$liveActivity['id']] = [
                'cloudStatisticData'=>[
                    'memberRequestTime' => $liveActivity['liveEndTime'],
                    'teacherId' => empty($liveActivity['anchorId']) ? 0 : $liveActivity['anchorId'],
                    'startTime' => $liveActivity['liveStartTime'],
                    'endTime' => $liveActivity['liveEndTime'],
                    'length' => round(($liveActivity['liveEndTime']-$liveActivity['liveStartTime']) / 60, 1),
                    'requestTime' => $liveActivity['liveEndTime'],
                    'maxOnlineNumber' => 0,
                    'checkinNum' => 0,
                    'chatNumber' => 0,
                    'memberNumber' => $count,
                    'avgWatchTime' => empty($count) || empty($time) ? 0 :round($time / ($count * 60), 1),
                    'detailFinished' => 1,
                    'memberFinished' => 1,
                ]
            ];
        }
        if(!empty($update)){
            $this->getLiveActivityDao()->batchUpdate(array_keys($update), $update, 'id');
        }
        $this->logger('info', '修改cloudStatisticData数据');
        return $page+1;
    }

    public function processActivityLiveProgressStatus($page)
    {
        $liveActivities = $this->getLiveActivityDao()->search([],['id'=>'ASC'],($page-1) * 500, 500);
        if(empty($liveActivities)){
            return 1;
        }
        $update = [];
        foreach ($liveActivities as $liveActivity){
            $status = 'created';
            if($liveActivity['liveStartTime'] > time()){
                $status = 'created';
            }
            if($liveActivity['liveStartTime'] < time() && $liveActivity['liveEndTime'] > time()){
                $status = 'live';
            }
            if($liveActivity['liveEndTime'] < time()){
                $status = 'closed';
            }
            $update[$liveActivity['id']] = [
                'progressStatus' => $status
            ];
        }

        if(!empty($update)){
            $this->getLiveActivityDao()->batchUpdate(array_keys($update), $update, 'id');
        }
        $this->logger('info', '修改progressStatus数据');
        return $page+1;
    }

    public function registerCallbackUrl()
    {
        try {
            $site = $this->getSettingService()->get('site', []);
            if (empty($site['url'])) {
                return 1;
            }
            $client = new EdusohoLiveClient();
            $client->uploadCallbackUrl(rtrim($site['url'], '/').'/callback/live/handle');
            $this->logger('info', '修改直播回调');
        } catch (\RuntimeException $e) {
        }

        return 1;
    }

    /**
     * @return \Biz\User\Dao\UserDao
     */
    protected function getUserDao()
    {
        return $this->createDao('User:UserDao');
    }

    /**
     * @return MarkerDao
     */
    protected function getMarkerDao()
    {
        return $this->createDao('Marker:MarkerDao');
    }

    /**
     * @return LiveMemberStatisticsDao
     */
    protected function getLiveMemberStatisticsDao()
    {
        return $this->createDao('LiveStatistics:LiveMemberStatisticsDao');
    }

    /**
     * @return LiveStatisticsDao
     */
    protected function getLiveStatisticsDao()
    {
        return $this->createDao('Live:LiveStatisticsDao');
    }

    /**
     * @return CourseDao
     */
    protected function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
    }

    /**
     * @return ActivityDao
     */
    protected function getActivityDao()
    {
        return $this->createDao('Activity:ActivityDao');
    }

    /**
     * @return LiveActivityDao
     */
    protected function getLiveActivityDao()
    {
        return $this->createDao('Activity:LiveActivityDao');
    }

    /**
     * @return \Biz\System\Service\CacheService
     */
    protected function getCacheService()
    {
        return $this->createService('System:CacheService');
    }

    /**
     * @return \Biz\Role\Service\RoleService
     */
    protected function getRoleService()
    {
        return $this->createService('Role:RoleService');
    }

    /**
     * @return \Codeages\Biz\Framework\Scheduler\Service\SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    protected function getTableCount($table)
    {
        $sql = "select count(*) from `{$table}`;";

        return $this->getConnection()->fetchColumn($sql) ?: 0;
    }

    protected function generateIndex($step, $page)
    {
        return $step * 1000000 + $page;
    }

    protected function getStepAndPage($index)
    {
        $step = intval($index / 1000000);
        $page = $index % 1000000;

        return array($step, $page);
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function isTableExist($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}` where key_name='{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function createIndex($table, $index, $column)
    {
        if (!$this->isIndexExist($table, $column, $index)) {
            $this->getConnection()->exec("ALTER TABLE {$table} ADD INDEX {$index} ({$column})");
        }
    }

    protected function isJobExist($code)
    {
        $sql = "select * from biz_scheduler_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function deleteCache()
    {
        $cachePath = $this->biz['cache_directory'];
        $filesystem = new Filesystem();
        $filesystem->remove($cachePath);

        clearstatcache(true);

        $this->logger('info', '删除缓存');

        return 1;
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}

abstract class AbstractUpdater
{
    protected $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function getConnection()
    {
        return $this->biz['db'];
    }

    protected function createService($name)
    {
        return $this->biz->service($name);
    }

    protected function createDao($name)
    {
        return $this->biz->dao($name);
    }

    abstract public function update();

    protected function logger($level, $message)
    {
        $version = \AppBundle\System::VERSION;
        $data = date('Y-m-d H:i:s') . " [{$level}] {$version} " . $message . PHP_EOL;
        if (!file_exists($this->getLoggerFile())) {
            touch($this->getLoggerFile());
        }
        file_put_contents($this->getLoggerFile(), $data, FILE_APPEND);
    }

    private function getLoggerFile()
    {
        return $this->biz['kernel.root_dir'] . '/../app/logs/upgrade.log';
    }

    /**
     * @return \Biz\DiscoveryColumn\Service\DiscoveryColumnService
     */
    protected function getDiscoveryColumnService()
    {
        return $this->createService('DiscoveryColumn:DiscoveryColumnService');
    }

    /**
     * @return \Biz\Taxonomy\Service\CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    /**
     * @return \Biz\System\Service\H5SettingService
     */
    protected function getH5SettingService()
    {
        return $this->createService('System:H5SettingService');
    }

    /**
     * @return \Biz\Course\Service\CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
