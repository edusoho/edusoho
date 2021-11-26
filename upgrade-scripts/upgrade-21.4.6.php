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
            'markerAddColumn',
            'activityLiveAddColumn',
            'addNewTable',
            'processMarkerData',
            'processActivityLiveTime',
            'processActivityLiveAnchorId',
            'processLiveStatisticsMemberData',
            'processLiveCloudStatisticData',
            'processActivityLiveProgressStatus',
            'registerJob',
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

    public function markerAddColumn()
    {
        if (!$this->isFieldExist('marker', 'activityIds')) {
            $this->getConnection()->exec("ALTER TABLE `marker` ADD COLUMN `activityIds` text COMMENT 'activityIds';");
            $this->logger('info', '弹题新增字段');
        }
        return 1;
    }

    public function activityLiveAddColumn(){
        if (!$this->isFieldExist('activity_live', 'replayStatus')) {
            $this->getConnection()->exec("ALTER TABLE `activity_live` MODIFY COLUMN `replayStatus` enum('ungenerated','generating','generated','videoGenerated','failure') NOT NULL DEFAULT 'ungenerated' COMMENT '回放状态';");
        }
        if (!$this->isFieldExist('activity_live', 'liveStartTime')) {
            $this->getConnection()->exec("ALTER TABLE `activity_live` ADD COLUMN `liveStartTime` int(10) NOT NULL DEFAULT 0 COMMENT '直播开始时间';");
        }
        if (!$this->isFieldExist('activity_live', 'liveEndTime')) {
            $this->getConnection()->exec("ALTER TABLE `activity_live` ADD COLUMN `liveEndTime` int(10) NOT NULL DEFAULT 0 COMMENT '直播开始时间';");
        }
        if (!$this->isFieldExist('activity_live', 'replayTagIds')) {
            $this->getConnection()->exec("ALTER TABLE `activity_live` ADD COLUMN `replayTagIds` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '回放标签ID';");
        }
        if (!$this->isFieldExist('activity_live', 'replayPublic')) {
            $this->getConnection()->exec("ALTER TABLE `activity_live` ADD COLUMN `replayPublic` tinyint(4) NOT NULL DEFAULT 0 COMMENT '回放是否共享';");
        }
        if (!$this->isFieldExist('activity_live', 'anchorId')) {
         $this->getConnection()->exec("ALTER TABLE `activity_live` ADD COLUMN `anchorId` int(10) NOT NULL DEFAULT 0 COMMENT '主讲人Id';");
        }
        if (!$this->isFieldExist('activity_live', 'cloudStatisticData')) {
            $this->getConnection()->exec("ALTER TABLE `activity_live` ADD COLUMN `cloudStatisticData` text COMMENT '直播数据';");
        }
        if (!$this->isFieldExist('live_statistics', 'classroomGroupId')) {
            $this->getConnection()->exec("ALTER TABLE `live_statistics` ADD COLUMN `classroomGroupId` int(10) NOT NULL DEFAULT 0 COMMENT '班级分组id';");
        }
        if (!$this->isFieldExist('open_course', 'replayEnable')) {
            $this->getConnection()->exec("ALTER TABLE `open_course` ADD COLUMN `replayEnable` tinyint(3) DEFAULT 1 COMMENT '是否允许观看回放';");
        }
        $this->logger('info', '添加字段');
        return 1;
    }

    public function addNewTable()
    {
        $this->getConnection()->exec("
             CREATE TABLE IF NOT EXISTS `activity_replay`  (
                  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                  `finish_type` varchar(32) NOT NULL DEFAULT 'end' COMMENT '完成类型',
                  `finish_detail` varchar(32) NOT NULL DEFAULT '' COMMENT '完成条件',
                  `origin_lesson_id` int(10) NOT NULL DEFAULT 0 COMMENT '引用课时ID',
                  `created_time` int(10) NOT NULL COMMENT '创建时间',
                  `updated_time` int(10) NOT NULL COMMENT '最后更新时间',
                  PRIMARY KEY (`id`)
                  )ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT = '直播回放活动';
             ");

        $this->getConnection()->exec("
             CREATE TABLE IF NOT EXISTS `classroom_live_group`  (
                  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                  `classroom_id` int(10) NOT NULL COMMENT '班级ID',
                  `live_code` varchar(64) NOT NULL DEFAULT '' COMMENT '直播分组ID',
                  `live_id` int(10) NOT NULL DEFAULT 0 COMMENT '直播ID',
                  `created_time` int(10) NOT NULL COMMENT '创建时间',
                  PRIMARY KEY (`id`)
                  )ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT = '班级直播分组';  
             ");

        $this->getConnection()->exec("
             CREATE TABLE IF NOT EXISTS `live_statistics_member_data` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `liveId` int(11) DEFAULT NULL,
                  `firstEnterTime` int(11) DEFAULT NULL,
                  `watchDuration` int(11) DEFAULT 0,
                  `checkinNum` int(11) DEFAULT 0,
                  `createdTime` int(11) DEFAULT NULL,
                  `updatedTime` int(11) DEFAULT NULL,
                  `requestTime` int(11) DEFAULT 0,
                  `userId` int(11) DEFAULT 0,
                  `courseId` int(11) DEFAULT 0,
                  `chatNum` int(11) DEFAULT 0,
                  `answerNum` int(11) DEFAULT 0,
                  UNIQUE KEY `courseId_userId_liveId` (`courseId`,`userId`,`liveId`),
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
             ");
        $this->logger('info', '新建表');
        return 1;
    }

    public function processMarkerData($page)
    {
        $limit = 50;
        $markers = $this->getMarkerDao()->search([], [], ($page-1) * $limit, $limit);
        if(empty($markers)){
          return  1;
        }
        $mediaIds = \AppBundle\Common\ArrayToolkit::column($markers, 'mediaId');
        $mediaIds = implode(',',$mediaIds);
        $sql = "select m.mediaId,c.activityId from marker m join (SELECT a.id as activityId,b.mediaId as fileId from activity a join activity_video b  on a.mediaId = b.id and a.mediaType = 'video') c  on m.`mediaId` =c.fileId  where m.mediaId in ({$mediaIds})";
        $result  = $this->getConnection()->fetchAll($sql, array());
        $result = \AppBundle\Common\ArrayToolkit::group($result, 'mediaId');
        $update = [];
        foreach ($markers as $marker){
            if(empty($result[$marker['mediaId']])){
                continue;
            }
            $activityIds = \AppBundle\Common\ArrayToolkit::column($result[$marker['mediaId']], 'activityId');
            $update[$marker['id']] = [
                'activityIds' => array_unique($activityIds)
            ];
        }
        if(!empty($update)){
            $this->getMarkerDao()->batchUpdate(array_keys($update), $update, 'id');
        }
        $this->logger('info', '处理弹题');
        return $page +1;
    }

    public function processActivityLiveTime()
    {
        $this->getConnection()->exec("update activity_live a  join activity b on a.id = b.mediaId set a.liveEndTime = b.endTime,a.liveStartTime=b.startTime where b.mediaType = 'live';");
        $this->getConnection()->exec("delete from live_statistics_member_data");
        $this->logger('info', '修改直播时间/删除live_statistics_member_data数据');
        return 1;
    }

    public function processActivityLiveAnchorId($page)
    {
        $liveActivities = $this->getLiveActivityDao()->search(['anchorId'=>0],['id'=>'ASC'],($page-1) * 500, 500, ['id']);
        if(empty($liveActivities)){
            return 1;
        }
        $liveActivities = \AppBundle\Common\ArrayToolkit::index($liveActivities, 'id');
        $ids = \AppBundle\Common\ArrayToolkit::column($liveActivities, 'id');
        $activities = $this->getActivityDao()->search(['mediaIds'=>$ids, 'mediaType' => 'live'], ['createdTime'=>'ASC'], 0, count($ids), ['id','mediaId', 'fromCourseId', 'fromUserId']);
        $courseIds = \AppBundle\Common\ArrayToolkit::column($activities, 'fromCourseId');
        $courses = $this->getCourseDao()->search(['ids' => $courseIds], [], 0, count($courseIds), ['id', 'teacherIds']);
        $courses = \AppBundle\Common\ArrayToolkit::index($courses, 'id');
        $update = [];
        foreach ($activities as $activity){
            if(empty($liveActivities[$activity['mediaId']]) || empty($courses[$activity['fromCourseId']])){
                continue;
            }
            $update[$liveActivities[$activity['mediaId']]['id']] = [
                'anchorId' => empty($courses[$activity['fromCourseId']]['teacherIds']) ? $activity['fromUserId'] : $courses[$activity['fromCourseId']]['teacherIds'][0],
            ];
        }
        if(!empty($update)){
            $this->getLiveActivityDao()->batchUpdate(array_keys($update), $update, 'id');
        }
        $this->logger('info', '修改liveActivity讲师');
        return $page+1;
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
        $activities = $this->getActivityDao()->search(['mediaIds' => $activityIds, 'mediaType'=> 'live'], [], 0, count($activityIds));
        $activities = \AppBundle\Common\ArrayToolkit::index($activities, 'mediaId');
        $users = $this->getUserDao()->search([],[], 0, 1);
        $userId = $users[0]['id'];
        foreach ($lives as $live){
            $count = $this->getLiveStatisticsDao()->count(['liveId'=>$live['liveId']]);
            if($count == 0 ||empty($liveActivities[$live['liveId']]) || empty($activities[$liveActivities[$live['liveId']]['id']])){
                continue;
            }
            $activity = $activities[$liveActivities[$live['liveId']]['id']];
            $create = [];
            if(empty($live['data']['detail'])){
                continue;
            }
            foreach ($live['data']['detail'] as $user){
                if($user['userId'] > $userId || !empty($create[$live['liveId'].'-'.$user['userId']]) || empty($user['firstJoin'])){
                    continue;
                }
                $create[$live['liveId'].'-'.$user['userId']] = [
                    'liveId' => $live['liveId'],
                    'userId' => $user['userId'] > $userId ? 0: $user['userId'],
                    'firstEnterTime' => $user['firstJoin'],
                    'watchDuration'=> $user['learnTime'],
                    'checkinNum' => 0,
                    'requestTime' => time(),
                    'courseId' => $activity['fromCourseId'],
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
        $anchorIds = \AppBundle\Common\ArrayToolkit::column($liveActivities, 'anchorId');
        $users = $this->getUserDao()->findByIds($anchorIds);
        $users = \AppBundle\Common\ArrayToolkit::index($users, 'id');
        $update=[];
        foreach ($liveActivities as $liveActivity){
            $time = $this->getLiveMemberStatisticsDao()->sumWatchDurationByLiveId($liveActivity['liveId']);
            $count = $this->getLiveMemberStatisticsDao()->count(['liveId'=>$liveActivity['liveId']]);
            if(date("Y-m-d", strtotime("-1 day")) == date('Y-m-d', $liveActivity['liveStartTime'])){
                continue;
            }
            $update[$liveActivity['id']] = [
                'cloudStatisticData'=>[
                    'memberRequestTime' => $liveActivity['liveEndTime'],
                    'teacher' => empty($liveActivity['anchorId']) ? '--':$users[$liveActivity['anchorId']]['nickname'],
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
        $liveActivities = $this->getLiveActivityDao()->search([],['id'=>'ASC'],($page-1) * 500, 500, ['id']);
        if(empty($liveActivities)){
            return 1;
        }
        $update = [];
        foreach ($liveActivities as $liveActivity){
            $update[$liveActivity['id']] = [
                'progressStatus' => $liveActivity['liveEndTime'] < time() ? 'closed' : $liveActivity['progressStatus']
            ];
        }

        if(!empty($update)){
            $this->getLiveActivityDao()->batchUpdate(array_keys($update), $update, 'id');
        }
        $this->logger('info', '修改progressStatus数据');
        return $page+1;
    }

    public function registerJob(){
        $count = $this->getSchedulerService()->countJobs(array('name' => 'DaySyncLiveDataJob'));
        if($count >0){
            return 1;
        }
        $xapiRandNum1 = rand(1, 59);
        $startJob = [
            'name' => 'DaySyncLiveDataJob',
            'expression' => "{$xapiRandNum1} 3 * * *",
            'class' => 'Biz\LiveStatistics\Job\DaySyncLiveDataJob',
            'misfire_threshold' => 10 * 60,
            'args' => [],
        ];

        $this->getSchedulerService()->register($startJob);
        return 1;
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
