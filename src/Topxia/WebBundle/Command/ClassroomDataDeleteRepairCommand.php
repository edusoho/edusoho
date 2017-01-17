<?php
namespace Topxia\WebBundle\Command;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Topxia\Service\User\CurrentUser;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;


class ClassroomDataDeleteRepairCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:classroom:DeleteRepair');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>查询删除超时时，未删除的同步班级课时~</info>');
        //拿到原课时不存在的课时
        $lessons = $this->findLessonsCopyIdNotExist();

        //找到有问题的课程
        $questionCourseIds = $this->findQuestionCoursesByLessons($lessons);

        //找到有问题的lesson
        $questionLessons = $this->findQuestionLessonsByCourseIds($questionCourseIds);

        //找到问题课时的学员数
        $this->findQuestionLessonMembersByLessons($questionLessons);

        // $publishQuestionLessons = $this->findPublishQuestionLessonsByCourseIds($questionCourseIds);
        // var_dump(count($publishQuestionLessons));

        $this->addLog('问题课时有'.count($questionLessons).'个');
        // foreach ($questionLessons as $questionLesson) {
        //     $this->addLog($questionLesson['id']);
        // }
        $output->writeln('<info>结束~</info>');
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
        $sql = "select * from course_lesson where ((copyId not in (SELECT id from course_lesson WHERE copyId = 0)) and copyId !=0)";
        return $this->getConnectionDb()->fetchAll($sql);
    }

    private function findQuestionCoursesByCourseIds($courseIds)
    {
        $courseIds = array_values($courseIds);
        $marks = str_repeat('?,', count($courseIds) - 1).'?';
        $sql = "select * from course where (id in ({$marks}) and locked = 1)";
        return $this->getConnectionDb()->fetchAll($sql, $courseIds);
    }

    private function findQuestionLessonsByCourseIds($courseIds)
    {
        $courseIds = array_values($courseIds);
        $marks = str_repeat('?,', count($courseIds) - 1).'?';
        $sql = "select * from course_lesson where ((copyId not in (SELECT id from course_lesson WHERE copyId = 0)) and copyId !=0) and courseId in ({$marks})";
        return $this->getConnectionDb()->fetchAll($sql, $courseIds);
    }

    private function findPublishQuestionLessonsByCourseIds($courseIds)
    {
        $courseIds = array_values($courseIds);
        $marks = str_repeat('?,', count($courseIds) - 1).'?';
        $sql = "select * from course_lesson where ((copyId not in (SELECT id from course_lesson WHERE copyId = 0)) and copyId !=0) and courseId in ({$marks}) and status = 'published'";
        return $this->getConnectionDb()->fetchAll($sql, $courseIds);
    }

    private function addLog($message)
    {
        $logger = new Logger('classroomDeleteQuestionData');
        $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/classroom-question-lessons.log', Logger::DEBUG));
        $logger->addInfo($message);
    }


    private function getConnectionDb()
    {
        $biz = $this->getBiz();
        return $biz['db'];
    }

    private function getBiz()
    {
         $biz = $this->getApplication()->getKernel()->getContainer()->get('biz');
         return $biz;
    }
}
