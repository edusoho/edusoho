<?php

namespace AppBundle\Command;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Dao\ActivityDao;
use Biz\Activity\Dao\LiveActivityDao;
use Biz\Course\Dao\CourseMemberDao;
use Biz\Live\Dao\LiveStatisticsDao;
use Biz\Live\Service\LiveStatisticsService;
use Biz\Util\EdusohoLiveClient;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class LiveStatisticsCommand extends BaseCommand
{
    private $teacherIds = array();

    protected function configure()
    {
        $this->setName('live:statistics');
            // ->addArgument('startTime', InputArgument::REQUIRED, '开始时间')
            // ->addArgument('endTime', InputArgument::REQUIRED, '结束时间');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>开始同步数据</info>');

        // $conditions = array(
        //     'startTime_GT' => strtotime($input->getArgument('startTime')),
        //     'endTime_LT' => strtotime($input->getArgument('endTime')),
        //     'mediaType' => 'live'
        // );

        // $activities = $this->getActivityDao()->search($conditions, array(), 0, PHP_INT_MAX, array('mediaId'));
        // $liveActivityIds = ArrayToolkit::column($activities, 'mediaId');

        // $liveActivities = $this->getLiveActivityDao()->search(array('ids' => $liveActivityIds, 'progressStatus' => 'closed'), array(), 0, PHP_INT_MAX, array('liveId'));

        // $liveIds = ArrayToolkit::column($liveActivities, 'liveId');
	    $liveIds = array(535090);
        try {
            foreach ($liveIds as $liveId) {
                $output->writeln('正在处理的直播间id: ' . $liveId);
                $this->teacherIds = $this->getTeacherIdsByLiveId($liveId);

                $output->writeln('正在处理直播间: ' . $liveId.'的点名数据');
                $checkinResult = $this->getLiveClient()->getLiveRoomCheckinList($liveId);

                $checkinData = $this->handleResult($checkinResult, LiveStatisticsService::STATISTICS_TYPE_CHECKIN);

                $checkinStatistics = array(
                    'liveId' => $liveId,
                    'type' => LiveStatisticsService::STATISTICS_TYPE_CHECKIN,
                    'data' => $checkinData,
                );

                $this->updateCheckinStatistics($liveId, $checkinStatistics);

                $output->writeln('正在处理直播间: ' . $liveId.'的访问数据');
                $visitorResult = $this->getLiveClient()->getLiveRoomHistory($liveId);

                $visitorData = $this->handleResult($visitorResult, LiveStatisticsService::STATISTICS_TYPE_VISITOR);

                $visitorStatistics = array(
                    'liveId' => $liveId,
                    'type' => LiveStatisticsService::STATISTICS_TYPE_VISITOR,
                    'data' => $visitorData,
                );

                $this->updateVisitorStatistics($liveId, $visitorStatistics);

                sleep(2);
            }
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }

        $output->writeln('<info>数据同步完毕</info>');
    }

    protected function updateCheckinStatistics($liveId, $statistics)
    {
        $existed = $this->getLiveStatisticsDao()->getByLiveIdAndType($liveId, LiveStatisticsService::STATISTICS_TYPE_CHECKIN);

        if (empty($existed)) {
            return $this->getLiveStatisticsDao()->create($statistics);
        }

        return empty($statistics['data']['detail']) ? $existed : $this->getLiveStatisticsDao()->update($existed['id'], $statistics);
    }

    protected function updateVisitorStatistics($liveId, $statistics)
    {
        $existed = $this->getLiveStatisticsDao()->getByLiveIdAndType($liveId, LiveStatisticsService::STATISTICS_TYPE_VISITOR);
        if (empty($existed)) {
            return $this->getLiveStatisticsDao()->create($statistics);
        }

        return empty($statistics['data']['detail']) ? $existed : $this->getLiveStatisticsDao()->update($existed['id'], $statistics);
    }

    protected function handleResult($result, $type)
    {
        try {
            $this->checkResult($result);

            if (LiveStatisticsService::STATISTICS_TYPE_CHECKIN == $type) {
                return $this->handleCheckinData($result['data']);
            }

            if (LiveStatisticsService::STATISTICS_TYPE_VISITOR == $type) {
                return $this->handleVisitorData($result['data']);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected function checkResult($result)
    {
        if (!isset($result['code'])) {
            throw new \Exception('code is not found');
        }

        if (!in_array($result['code'], array(0, 4001, 4003))) {
            throw new \Exception('code is not valid');
        }

        if (!isset($result['data'])) {
            throw new \Exception('data is not found');
        }

        return true;
    }

    protected function handleCheckinData($data)
    {
        if (empty($data)) {
            return array('success' => 1);
        }

        try {
            foreach ($data[0]['users'] as &$user) {
                $user = $this->handleUser($user);

                if (empty($user)) {
                    unset($user);
                }
            }
        } catch (\Exception $e) {
            return array(
                'time' => intval($data[0]['time'] / 1000),
                'success' => 0,
                'detail' => array(),
            );
        }

        return array(
            'time' => intval($data[0]['time'] / 1000),
            'success' => 1,
            'detail' => $data[0]['users'],
        );
    }

    protected function handleVisitorData($data)
    {
        if (empty($data)) {
            return array('success' => 1);
        }

        $result = array();
        $totalLearnTime = 0;
        try {
            foreach ($data as $user) {
                $user = $this->handleUser($user);
                if (empty($user)) {
                    continue;
                }

                $result = $this->handleUserResult($result, $user);
                $totalLearnTime += ($user['leaveTime'] - $user['joinTime'])/1000;
            }
        } catch (\Exception $e) {
            return array(
                'totalLearnTime' => 0,
                'success' => 0,
                'detail' => array(),
            );
        }

        return array(
            'totalLearnTime' => $totalLearnTime,
            'success' => 1,
            'detail' => $result,
        );
    }

    private function handleUserResult($result, $user)
    {
        $userId = $user['userId'];
        $user['joinTime'] /= 1000;
        $user['leaveTime'] /= 1000;

        if (empty($result[$userId])) {
            $result[$userId] = array(
                'userId' => $userId,
                'nickname' => $user['nickname'],
                'firstJoin' => $user['joinTime'],
                'lastLeave' => $user['leaveTime'],
                'learnTime' => $user['leaveTime'] - $user['joinTime'],
            );
        } else {
            $result[$userId] = array(
                'userId' => $userId,
                'nickname' => $user['nickname'],
                'firstJoin' => $result[$userId]['firstJoin'] > $user['joinTime'] ? $user['joinTime'] : $result[$userId]['firstJoin'],
                'lastLeave' => $result[$userId]['lastLeave'] > $user['leaveTime'] ? $result[$userId]['lastLeave'] : $user['leaveTime'],
                'learnTime' => $result[$userId]['learnTime'] + ($user['leaveTime'] - $user['joinTime']),
            );
        }

        return $result;
    }

    protected function handleUser($user)
    {
	    $userId = trim(strrchr($user['nickName'], '_'), '_');
        if (empty($userId)) {
            $existedUser = $this->getUserService()->getUserByNickname($user['nickName']);
        } else {
            $existedUser = array(
                'id' => $userId,
                'nickname' => strstr($user['nickName'], '_', true),
            );
        }
        
        if (empty($existedUser)) {
            return array();
//            throw new \Exception('User not found');
        }

        if (in_array($existedUser['id'], $this->teacherIds)) {
            return array();
        }

        $user['nickname'] = $existedUser['nickname'];
        $user['userId'] = $existedUser['id'];

        return $user;
    }

    private function getTeacherIdsByLiveId($liveId)
    {
        $liveActivity = $this->getLiveActivityDao()->getByLiveId($liveId);
        if (empty($liveActivity)) {
            return array();
        }

        $activity = $this->getActivityDao()->getByMediaIdAndMediaTypeAndCopyId($liveActivity['id'], 'live', 0);

        if (empty($activity)) {
            return array();
        }

        $teachers = $this->getCourseMemberDao()->findByCourseIdAndRole($activity['fromCourseId'], 'teacher');

        return ArrayToolkit::column($teachers, 'userId');
    }

    /**
     * @return EdusohoLiveClient
     */
    protected function getLiveClient()
    {
        $biz = $this->getBiz();
        return $biz['educloud.live_client'];
    }

    /**
     * @return LiveStatisticsDao
     */
    protected function getLiveStatisticsDao()
    {
        return $this->getBiz()->dao('Live:LiveStatisticsDao');
    }

    /**
     * @return LiveActivityDao
     */
    private function getLiveActivityDao()
    {
        return $this->getBiz()->dao('Activity:LiveActivityDao');
    }

    /**
     * @return ActivityDao
     */
    private function getActivityDao()
    {
        return $this->getBiz()->dao('Activity:ActivityDao');
    }

    /**
     * @return CourseMemberDao
     */
    private function getCourseMemberDao()
    {
        return $this->getBiz()->dao('Course:CourseMemberDao');
    }
}


