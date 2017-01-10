<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;


class ClassroomDataRepairCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:classroom:Repair1');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>修复数据开始~</info>');
        $output->writeln('<info>正在查询班级下所有的课程的异常课时...</info>');

        $this->initServiceKernel();
        //拿到需要处理的课程（原课程id，复制出来的课程Id）
        $classroomCourse = $this->getClassroomCourse();
        $sourceCourseIds = ArrayToolkit::column($classroomCourse,'parentId');
        $uniquenessCourseIds = array();
        foreach ($sourceCourseIds as $key => $value) {
            if(!empty($uniquenessCourseIds[$value])){
                continue;
            }
            $uniquenessCourseIds[$value]['courseId'] = $value;
            $uniquenessCourseIds[$value]['lessons'] =  $this->getCourseService()->getCourseLessons($value);
        }

        //每一个需要处理的课程
        foreach ($uniquenessCourseIds as $key => $course) {
           $lessons = $course['lessons'];
           foreach ($lessons as $key => $lesson) {
            $output->writeln('<info>处理原课程Id：'.$course['courseId'].',课时Id:'.$lesson['id'].'</info>');
               $this->syncLessonsBySourseCourseIdAndLessonId($course['courseId'],$lesson['id']);
           }
        }

        $output->writeln('<info>结束~</info>');
    }

    private function syncLessonsBySourseCourseIdAndLessonId($courseId,$lesonId)
    {
        $lesson = $this->getCourseService()->getLesson($lesonId);
        if (!empty($lesson) && $lesson['type'] == 'testpaper') {
            unset($lesson['mediaId']);
        }

        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId, 1), 'id');

        if ($courseIds) {
            $classroomLessons = $this->getCourseService()->findLessonsByCopyIdAndLockedCourseIds($lesson['id'], $courseIds);
            $classroomLessons = ArrayToolkit::index($classroomLessons,'courseId');

            foreach ($courseIds as $key => $classroomCourseId) {
                var_dump("根据原课程Id：".$courseId.",原课时：".$lesonId."班级内课程ID：".$classroomCourseId.",的课时Id；".$classroomLessons[$classroomCourseId]['id']);
                $this->getCourseService()->updateLesson($classroomCourseId, $classroomLessons[$classroomCourseId]['id'], $lesson);
                
            }
        }

    }

    private function getClassroomCourse()
    {
        $sql = "select id ,parentId from course where parentId > 0 ";
        $resutl = $this->getConnectionDb()->fetchAll($sql);
        return $resutl;
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

    private function getBiz()
    {
         $biz = $this->getApplication()->getKernel()->getContainer()->get('biz');
         return $biz;
    }
}
