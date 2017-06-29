<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Topxia\Service\Common\ServiceKernel;
use AppBundle\Common\ArrayToolkit;

class LiveNotifyCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('topxia:live-notify')
            ->setDescription('直播通知');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();
        $biz = $this->getContainer()->get('biz');
        $connection = $biz['db'];

        $tomorrow = date('Y-m-d', strtotime('+1 day'));

        $startDate = $tomorrow.' 0:00:00';
        $endDate = $tomorrow.' 24:00:00';

        $conditions['startTimeLessThan'] = strtotime($endDate);
        $conditions['startTimeGreaterThan'] = strtotime($startDate);
        $total = $this->getCourseService()->searchLessonCount($conditions);

        $liveLessons = $this->getCourseService()->searchLessons(
            $conditions, array('startTime', 'ASC'), 0, $total
        );

        $courseIds = ArrayToolkit::column($liveLessons, 'courseId');
        $courseIds = array_unique($courseIds);
        $courseIds = array_values($courseIds);

        if ($courseIds) {
            $courseMembers = $this->getCourseMemberService()->findCourseStudentsByCourseIds($courseIds);

            foreach ($courseMembers as $key => $value) {
                $minStartTime = $this->getCourseService()->findMinStartTimeByCourseId($value['courseId']);

                if (time() >= strtotime($startDate)) {
                    $noticeDay = '今天';
                } else {
                    $noticeDay = '明天';
                }

                $minStartTime = date('Y-m-d H:i:s', $minStartTime[0]['startTime']);
                $message = array(
                  'noticeDay' => $noticeDay,
                  'minStartTime' => $minStartTime, );

                $this->getNotificationService()->notify($value['userId'], 'live-course', $message);
            }

            $output->writeln('<info>消息发布完成</info>');
        } else {
            $output->writeln('<info>没有消息可以发布</info>');
        }
    }

    protected function getNotificationService()
    {
        return ServiceKernel::instance()->createService('User:NotificationService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->getServiceKernel()->createService('Course:MemberService');
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }

    public function getCurrentUser()
    {
        return $this->getKernel()->getCurrentUser();
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }
}
