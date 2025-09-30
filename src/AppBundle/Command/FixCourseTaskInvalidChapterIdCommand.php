<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixCourseTaskInvalidChapterIdCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('fix:course-task-invalid-chapter-id')
            ->addArgument('id', InputArgument::REQUIRED, '课程ID(计划ID, courseId)')
            ->addOption(
                'real',
                InputArgument::OPTIONAL
            )
            ->setDescription('修复课程计划任务错误的章节ID（由于 BUG 导致数据出错，此BUG 25.3.2中已修复，请更新到最新版本），参见工单：102395。BUG 的现象：课时的任务无法编辑，任务无法排序。');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $courseId = $input->getArgument('id');
        $real = $input->getOption('real');

        $db = $this->getBiz()['db'];

        $updateIds = [];
        $deleteIds = [];
        $tasks = $db->fetchAll("SELECT * FROM course_task WHERE courseId = {$courseId} ORDER BY seq ASC");
        echo "共查询到 course_task " . count($tasks) . " 条数据。\n";
        foreach ($tasks as $task) {
            echo "[Task] seq:{$task['seq']}\tid: {$task['id']}\tcategoryId: {$task['categoryId']}\t title:{$task['title']}\t";
            $chapter = $db->fetchAssoc("SELECT * FROM course_chapter WHERE id = {$task['categoryId']}");
            if ($chapter && $chapter['courseId'] == $courseId) {
                echo "OK\n";
                continue;
            }
            if (!$chapter) {
                echo "ERROR: chapter 不存在 \n";
            } else {
                echo "ERROR: chapter 的 courseId 不一致\n";
            }
            $rightChapters = $db->fetchAll("SELECT * FROM course_chapter WHERE courseId = {$courseId} AND title = '{$task['title']}' ORDER BY id ASC");
            if (empty($rightChapters)) {
                $deleteIds[] = $task['id'];
                echo "       == 查询到可能正确的 chapter：无，说明这个Task应该被删除!\n";
                if ($real) {
                    $deleted = $db->delete('course_task', array('id' => $task['id']));
                    echo "       @@ 删除 task: id:{$task['id']}\t, deleted: {$deleted}\n";
                }
            } else {
                $updateIds[] = $task['id'];
                foreach ($rightChapters as $rightChapter) {
                    echo "       == 查询到可能正确的 chapter: id:{$rightChapter['id']}\ttype:{$rightChapter['type']}\tnumber:{$rightChapter['number']}\tseq:{$rightChapter['seq']}\ttitle:{$rightChapter['title']}\n";
                }
                if ($real) {
                    $updated = $db->update('course_task', array('categoryId' => $rightChapters[0]['id']), array('id' => $task['id']));
                    echo "       @@ 更新 task: id:{$task['id']}\t, updated: {$updated}\n";
                }
            }
        }

        echo "异常数据：" . (count($updateIds) + count($deleteIds)) . " 条。\n";
        echo "- 更新" . count($updateIds) . " 条。\n";
        echo "- 删除 " . count($deleteIds) . " 条。\n";

        echo "=====================================================================\n";
        echo "数据验证：不在 course_chapter 中的 course_task，这些 course_task 都是有问题的...\n";
        $tasks = $db->fetchAll("SELECT * FROM course_task WHERE courseId = {$courseId} AND categoryId NOT IN (SELECT id FROM course_chapter WHERE courseId = {$courseId})");
        echo "共查询到有问题的 course_task " . count($tasks) . " 条数据。\n";
        foreach ($tasks as $task) {
            echo "[Task] seq:{$task['seq']}\tid: {$task['id']}\tcategoryId: {$task['categoryId']}\t title:{$task['title']}\t";
            echo "\n";
        }
    }
}