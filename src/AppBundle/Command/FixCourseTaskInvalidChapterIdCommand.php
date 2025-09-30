<?php

namespace AppBundle\Command;

use AppBundle\Common\ArrayToolkit;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FixCourseTaskInvalidChapterIdCommand extends BaseCommand
{
    /**
     * @var Logger
     */
    private $logger;

    protected function configure()
    {
        $this->setName('fix:course-task-invalid-chapter-id')
            ->addArgument('id', InputArgument::REQUIRED, '课程ID(计划ID, courseId)')
            ->addOption('real', null, InputOption::VALUE_NONE, '是否进行真正的数据更新')
            ->addOption('only-display-error', null, InputOption::VALUE_NONE, "只显示出错的课程任务")
            ->setDescription('修复课程的某些任务无法编辑，任务无法排序的问题')
            ->setHelp(<<<'HELP'
修复课程的某些任务无法编辑，任务无法排序的问题。出现此问题的主要原因：
1. course_task 中 categoryId 对应的 course_chapter 不是当前课程的 （因为BUG，导致了 course_task.categoryId被错误的更新了)
2. course_task 中 存在的记录，但对应的 course_chapter 不存在。

第 1 种情况，此命令会尝试更新 course_task.categoryId 为正确的值（通过 title 匹配对应的 course_chapter）
第 2 中情况，直接删除对应的 course_task 记录。

(由于 BUG 导致此次 course_task 数据出错，此 BUG 于 25.3.2 中修复，参见工单 #102395。)

命令的使用：
* 修复某一个课程：app/console fix:course-task-invalid-chapter-id 课程ID
* 批量修复所有课程：app/console fix:course-task-invalid-chapter-id all

注意事项：请仔细核对日志的输出，核对完毕后，加上 --real 参数执行命令，此命令会进行真正的数据更新。 

HELP
);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $biz = $this->getBiz();
        $logger = new Logger('>');
        $logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
        $logger->pushHandler(new StreamHandler("{$biz['log_directory']}/fix-course-task-invalid-chapter-id.log", Logger::DEBUG));
        $this->logger = $logger;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->logger;
        $courseId = $input->getArgument('id');
        $onlyDisplayError = $input->getOption('only-display-error');
        $real = $input->getOption('real');
        $db = $this->getBiz()['db'];

        if ($courseId == 'all') {
            $courses = $db->fetchAll("SELECT * FROM course_v8 ORDER BY id ASC");
            $courseIds = ArrayToolkit::column($courses, 'id');
        } else {
            $courseIds = [$courseId];
        }

        $totalCourseCount = count($courseIds);
        $problemCourseCount = 0;
        $problemTaskCount = 0;
        $problemUpdateTaskCount = 0;
        $problemDeleteTaskCount = 0;

        $logger->info("共查询到 $totalCourseCount 个课程。");

        foreach ($courseIds as $courseId) {
            $logger->info("==============================");
            $logger->info("正在处理课程，ID: {$courseId}...");
            $course = $db->fetchAssoc("SELECT * FROM course_v8 WHERE id = {$courseId}");
            if (!$course) {
                $logger->error("ERROR: 课程不存在，ID: {$courseId}");
                continue ;
            } else {
                $logger->info("课程标题：{$course['title']}");
            }

            $updateCount = 0;
            $deleteCount = 0;
            $tasks = $db->fetchAll("SELECT * FROM course_task WHERE courseId = {$courseId} ORDER BY seq ASC");
            $logger->info("共查询到 course_task " . count($tasks) . " 条");
            foreach ($tasks as $task) {
                $chapter = $db->fetchAssoc("SELECT * FROM course_chapter WHERE id = {$task['categoryId']}");
                $log = "[Task] seq:{$task['seq']}\tid: {$task['id']}\tcategoryId: {$task['categoryId']}\t title:{$task['title']}";
                if ($chapter && $chapter['courseId'] == $courseId) {
                    if (!$onlyDisplayError) {
                        $logger->info("$log\tOK");
                    }
                    continue;
                }
                if (!$chapter) {
                    $logger->error("{$log}\tERROR: chapter 不存在");
                } else {
                    $logger->error("{$log}\tERROR: chapter 的 courseId 不一致");
                }
                $rightChapters = $db->fetchAll("SELECT * FROM course_chapter WHERE courseId = {$courseId} AND title = '{$task['title']}' ORDER BY id ASC");
                if (empty($rightChapters)) {
                    $deleteCount ++;
                    $logger->info("== 查询到可能正确的 chapter：无，说明这个Task应该被删除!");
                    if ($real) {
                        $deleted = $db->delete('course_task', array('id' => $task['id']));
                        $logger->info("@@ 删除 task: id:{$task['id']}\t, deleted: {$deleted}");
                        $logger->info("@@ DELETED TASK", $task);
                    }
                } else {
                    $updateCount ++;
                    foreach ($rightChapters as $rightChapter) {
                        $logger->info("== 查询到可能正确的 chapter: id:{$rightChapter['id']}\ttype:{$rightChapter['type']}\tnumber:{$rightChapter['number']}\tseq:{$rightChapter['seq']}\ttitle:{$rightChapter['title']}");
                    }
                    if ($real) {
                        $updated = $db->update('course_task', array('categoryId' => $rightChapters[0]['id']), array('id' => $task['id']));
                        $logger->info("@@ 更新 task: id:{$task['id']}\t, updated: {$updated}");
                    }
                }
            }

            $logger->info("异常数据：" . ($updateCount + $deleteCount) . " 条。");
            $logger->info("- 更新 " . $updateCount . " 条。");
            $logger->info("- 删除 " . $deleteCount . " 条。");

            $problemUpdateTaskCount += $updateCount;
            $problemDeleteTaskCount += $deleteCount;
            $problemTaskCount += $updateCount + $deleteCount;
            if ($updateCount + $deleteCount > 0) {
                $problemCourseCount ++;
            }

            $logger->info("验证当前课程数据：不在 course_chapter 中的 course_task，这些 course_task 都是有问题的...");
            $tasks = $db->fetchAll("SELECT * FROM course_task WHERE courseId = {$courseId} AND categoryId NOT IN (SELECT id FROM course_chapter WHERE courseId = {$courseId})");
            $logger->info("共查询到有问题的 course_task " . count($tasks) . " 条数据。");
            foreach ($tasks as $task) {
                $logger->info("[Task] seq:{$task['seq']}\tid: {$task['id']}\tcategoryId: {$task['categoryId']}\t title:{$task['title']}");
            }
        }
        $logger->info("==============================");
        $logger->info("==============================");
        $logger->info("课程总数：{$totalCourseCount}");
        $logger->info("有问题的课程数：{$problemCourseCount}");
        $logger->info("有问题的任务总数：{$problemTaskCount}");
        $logger->info("有问题的任务(待更新)总数：{$problemUpdateTaskCount}");
        $logger->info("有问题的任务(待删除)总数：{$problemDeleteTaskCount}");
        $logger->info("==============================");
        $logger->info("==============================");
        if (!$real) {
            $logger->info("请仔细检查日志输出的内容，核对无误后，加上 --real 参数执行命令，将数据更新到数据库中。");
            $logger->info("请仔细检查日志输出的内容，核对无误后，加上 --real 参数执行命令，将数据更新到数据库中。");
            $logger->info("请仔细检查日志输出的内容，核对无误后，加上 --real 参数执行命令，将数据更新到数据库中。");
        }
    }
}