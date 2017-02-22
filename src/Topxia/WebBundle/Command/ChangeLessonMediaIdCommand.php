<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Topxia\Common\Paginator;
use Topxia\Service\Common\ServiceKernel;


class ChangeLessonMediaIdCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('unit:change-lesson-mediaId');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>修复课时与试卷的无联系问题...</info>');

        $condition = array(
            'copyId' => 0,
            'type'   => 'testpaper',
            'startTime' => 1484064000
        );

        $sourceLessons = $this->findQuestionSourceLessons($condition);

        foreach ($sourceLessons as $sourceLesson) {
            $copyLessons = $this->findCopyLessons($sourceLesson['id']);
            foreach ($copyLessons as $copyLesson) {
                $target = "course-{$copyLesson['courseId']}";
                $trueTestPaper = $this->getTestPaperByCopyIdAndTarget($sourceLesson['mediaId'], $target);
                $this->updateCopyLessonMediaId($copyLesson['id'], $trueTestPaper['id']);
            }
        }
    }

    private function updateCopyLessonMediaId($lessonId, $mediaId)
    {
        $sql = "update course_lesson set mediaId = {$mediaId} where id = {$lessonId}";
        return $this->db()->executeQuery($sql, array());
    }

    private function getTestPaperByCopyIdAndTarget($copyId, $target)
    {
        $sql = "select * from testpaper where copyId = ? and target = ?";
        return $this->db()->fetchAssoc($sql, array($copyId, $target));
    }

    private function findQuestionSourceLessons($condition)
    {
        $lessonCount = $this->getCourseService()->searchLessonCount($condition);
        $sourceLessons = $this->getCourseService()->searchLessons(
            $condition, 
            array('createdTime', 'DESC'), 
            0, 
            $lessonCount
        );

        return $sourceLessons;
    }

    private function findCopyLessons($lessonId)
    {
        $sql = "select * from course_lesson where copyId = {$lessonId}";
        return $this->db()->fetchAll($sql, array());
    }

    protected function findCopyQuestion($sourceQuestion)
    {
        return $this->getQuestionService()->findQuestionsByCopyIds(array($sourceQuestion['id']));
    }

    protected function getBiz()
    {
        return $this->getContainer()->get('biz');
    }

    protected function db()
    {
        $biz = $this->getBiz();
        return $biz['db'];
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }
}
