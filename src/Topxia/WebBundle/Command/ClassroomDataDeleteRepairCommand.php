<?php
namespace Topxia\WebBundle\Command;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Topxia\Service\User\CurrentUser;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Topxia\Service\Util\EdusohoLiveClient;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;


class ClassroomDataDeleteRepairCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:classroom:DeleteRepair')
            ->addArgument('code', InputArgument::OPTIONAL, '是否删除未删除数据');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>删除未删除的班级课时...</info>');

        //拿到原课时不存在的课时
        $lessons = $this->findLessonsCopyIdNotExist();
        $this->addLog('原课时不存在的课时有'.count($lessons).'个');

        if (!empty($lessons)) {
            //找到有问题的课程
            $questionCourseIds = $this->findQuestionCoursesByLessons($lessons);
            $this->addLog('有问题的课程有'.count($questionCourseIds).'个');

            //找到有问题的课时
            $questionLessons = $this->findQuestionLessonsByCourseIds($questionCourseIds);
            $this->addLog('有问题的课时有'.count($questionLessons).'个');

            //发布了的问题课时
            $publishQuestionLessons = ArrayToolkit::group($questionLessons, 'status');
            $this->addLog('发布了的问题课时有'.count($publishQuestionLessons['published']).'个');
            $this->addLog('未发布了的问题课时有'.count($publishQuestionLessons['unpublished']).'个');

            //找到问题课时的学员数
            $this->findQuestionLessonMembersByLessons($questionLessons);

            $code = $input->getArgument('code');
            if ($code === 'delete') {
                $this->initServiceKernel();
                $this->deleteQuestionLessons($questionLessons);
            }
        }
       
        $output->writeln('<info>结束~</info>');
    }

    private function deleteQuestionLessons($questionLessons)
    {
        foreach ($questionLessons as $lesson) {
            $course = $this->getCourseService()->tryManageCourse($lesson['courseId']);
            $lesson = $this->getCourseService()->getCourseLesson($lesson['courseId'], $lesson['id']);

            if ($course['type'] == 'live') {
                $client = new EdusohoLiveClient();

                if ($lesson['type'] == 'live') {
                    $result = $client->deleteLive($lesson['mediaId'], $lesson['liveProvider']);
                }

                $this->getCourseService()->deleteCourseLessonReplayByLessonId($lesson['id']);
            }

            //$this->getCourseDeleteService()->deleteLessonResult($lesson['mediaId']);
            $this->getCourseService()->deleteLesson($course['id'], $lesson['id']);

            if ($this->isPluginInstalled('Homework')) {
                //如果安装了作业插件那么也删除作业和练习
                $homework = $this->getHomeworkService()->getHomeworkByLessonId($lesson['id']);

                if (!empty($homework)) {
                    $this->getHomeworkService()->removeHomework($homework['id']);
                }

                $this->getExerciseService()->deleteExercisesByLessonId($lesson['id']);
            }
        }
    }

    private function findQuestionCoursesByLessons($lessons)
    {
        $courseIds = ArrayToolkit::column($lessons, 'courseId');
        $courseIds = array_unique($courseIds);

        $questionCourseIds = $this->findQuestionCoursesByCourseIds($courseIds);
        $questionCourseIds = ArrayToolkit::column($questionCourseIds, 'id');
        return $questionCourseIds;
    }

    private function findQuestionLessonMembersByLessons($lessons)
    {
        foreach ($lessons as $lesson) {
            $sql = "select count(*) as count from course_lesson_learn where lessonId={$lesson['id']}";
            $result = $this->getConnectionDb()->fetchAll($sql);
            $this->addLog('课时id为:'.$lesson['id'].'的学员数为'.$result[0]['count']);
        }
    }

    private function findLessonsCopyIdNotExist()
    {
        $sql = "select * from course_lesson where copyId not in (SELECT id from course_lesson) and copyId !=0";
        return $this->getConnectionDb()->fetchAll($sql);
    }

    private function findQuestionCoursesByCourseIds($courseIds)
    {
        $courseIds = array_values($courseIds);
        $marks = str_repeat('?,', count($courseIds) - 1).'?';
        $sql = "select * from course where id in ({$marks}) and locked = 1";
        return $this->getConnectionDb()->fetchAll($sql, $courseIds);
    }

    private function findQuestionLessonsByCourseIds($courseIds)
    {
        if (empty($courseIds)) {
            return array();
        }
        $courseIds = array_values($courseIds);
        $marks = str_repeat('?,', count($courseIds) - 1).'?';
        $sql = "select * from course_lesson where copyId not in (SELECT id from course_lesson) and copyId !=0 and courseId in ({$marks})";
        return $this->getConnectionDb()->fetchAll($sql, $courseIds);
    }

    private function addLog($message)
    {
        $logger = new Logger('classroomDeleteQuestionData');
        $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/classroom-question-lessons.log', Logger::DEBUG));
        $logger->addInfo($message);
    }

    protected function isPluginInstalled($name)
    {
        return $this->getApplication()->getKernel()->getContainer()->get('topxia.twig.web_extension')->isPluginInstalled($name);
    }

    protected function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Homework:Homework.HomeworkService');
    }

    protected function getExerciseService()
    {
        return $this->getServiceKernel()->createService('Homework:Homework.ExerciseService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getConnectionDb()
    {
        $biz = $this->getBiz();
        return $biz['db'];
    }

    protected function initServiceKernel()
    {
        $serviceKernel = ServiceKernel::create('dev', false);
        $serviceKernel->setParameterBag($this->getContainer()->getParameterBag());
        $serviceKernel->setBiz($this->getContainer()->get('biz'));

        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id'        => 1,
            'nickname'  => '游客',
            'currentIp' => '127.0.0.1',
            'roles'     => array()
        ));
        $serviceKernel->setCurrentUser($currentUser);
        $currentUser->setPermissions('admin_course_content_manage');
    }

    private function getBiz()
    {
         $biz = $this->getApplication()->getKernel()->getContainer()->get('biz');
         return $biz;
    }
}
