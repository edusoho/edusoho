<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class X8ScriptCheckCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:x8-script-check')
            ->setDescription('x8升级脚本校验');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $biz = $this->getContainer()->get('biz');
        $connection = $biz['db'];

        // 课程
        $c1 = $connection->fetchColumn('select count(*) from course;');
        $c2 = $connection->fetchColumn('select count(*) from course_v8;');
        $c3 = $connection->fetchColumn('select count(*) from course_set_v8;');
        if ($c1 == $c2 && $c2 == $c3) {
            $output->writeln("<info> 课程 数据验证通过，{$c1} {$c2} {$c3}.</info>");
        } else {
            $output->writeln("<error> 课程 数据验证不通过，{$c1} {$c2} {$c3}.</error>");
        }

        // 试卷
        $c1 = $connection->fetchColumn('select count(*) from testpaper;');
        $c2 = $connection->fetchColumn("select count(*) from testpaper_v8 where type='testpaper';");
        if ($c1 == $c2) {
            $output->writeln("<info> 试卷 数据验证通过，{$c1} {$c2}.</info>");
        } else {
            $output->writeln("<error> 试卷 数据验证不通过，{$c1} {$c2}.</error>");
        }

        $c1 = $connection->fetchColumn("select count(*) from activity where mediaType = 'testpaper';");
        $c2 = $connection->fetchColumn("select count(*) from course_task where activityId in (select id from activity where mediaType = 'testpaper');");
        $c3 = $connection->fetchColumn('select count(*) from activity_testpaper;');
        if ($c1 == $c2 && $c2 == $c3) {
            $output->writeln("<info> 试卷活动 数据验证通过，{$c1} {$c2} {$c3}.</info>");
        } else {
            $output->writeln("<error> 试卷活动 数据验证不通过，{$c1} {$c2} {$c3}.</error>");
        }

        $c1 = $connection->fetchColumn('select count(*) from testpaper_item;');
        $c2 = $connection->fetchColumn("select count(*) from testpaper_item_v8 where testId in (select id from testpaper_v8 where type='testpaper') and type='testpaper';");
        if ($c1 == $c2) {
            $output->writeln("<info> 试卷中题目 数据验证通过，{$c1} {$c2}.</info>");
        } else {
            $output->writeln("<error> 试卷中题目 数据验证不通过，{$c1} {$c2}.</error>");
        }

        // 作业：
        $c1 = $connection->fetchColumn('select count(*) from homework where lessonId in (select id from course_lesson) and id in (select max(id) from homework group by lessonId);');
        $c2 = $connection->fetchColumn("select count(*) from testpaper_v8 as t left join homework as h on t.migrateTestId = h.id where t.type='homework' and h.lessonId in (select id from course_lesson) and t.migrateTestId IN (select max(id) from homework group by lessonId);");
        $c3 = $connection->fetchColumn("select count(*) from activity where mediaType = 'homework';");
        $c4 = $connection->fetchColumn("select count(*) from course_task where activityId in (select id from activity where mediaType = 'homework');");
        if ($c1 == $c2 && $c2 == $c3 && $c3 == $c4) {
            $output->writeln("<info> 作业 数据验证通过，{$c1} {$c2} {$c3} {$c4}.</info>");
        } else {
            $output->writeln("<error> 作业 数据验证不通过，{$c1} {$c2} {$c3} {$c4}.</error>");
        }

        $c1 = $connection->fetchColumn('select count(*) from homework_item where homeworkId in (select id from homework);');
        $c2 = $connection->fetchColumn("select count(*) from testpaper_item_v8 where testId in (select id from testpaper_v8 where type='homework') and type='homework';");
        if ($c1 == $c2) {
            $output->writeln("<info> 作业中题目 数据验证通过，{$c1} {$c2}.</info>");
        } else {
            $output->writeln("<error> 作业中题目 数据验证不通过，{$c1} {$c2}.</error>");
        }

        // 练习：
        $c1 = $connection->fetchColumn('select count(*) from exercise where lessonId in (select id from course_lesson) and id in(select max(id) from exercise group by lessonId);');
        $c2 = $connection->fetchColumn("select count(*) from testpaper_v8 as t left join exercise as h on t.migrateTestId = h.id where t.type='exercise' and h.lessonId in (select id from course_lesson) and t.migrateTestId IN (select max(id) from exercise group by lessonId);");
        $c3 = $connection->fetchColumn("select count(*) from activity where mediaType = 'exercise';");
        $c4 = $connection->fetchColumn("select count(*) from course_task where activityId in (select id from activity where mediaType = 'exercise');");
        if ($c1 == $c2 && $c2 == $c3 && $c3 == $c4) {
            $output->writeln("<info> 练习 数据验证通过，{$c1} {$c2} {$c3} {$c4}.</info>");
        } else {
            $output->writeln("<error> 练习 数据验证不通过，{$c1} {$c2} {$c3} {$c4}.</error>");
        }

        $c1 = $connection->fetchColumn('select count(*) from exercise_item where exerciseId in (select id from exercise);');
        $c2 = $connection->fetchColumn("select count(*) from testpaper_item_v8 where testId in (select id from testpaper_v8 where type='exercise') and type='exercise';");
        if ($c1 == $c2) {
            $output->writeln("<info> 练习中题目 数据验证通过，{$c1} {$c2}.</info>");
        } else {
            $output->writeln("<error> 练习中题目 数据验证不通过，{$c1} {$c2}.</error>");
        }

        // 视频：
        $c1 = $connection->fetchColumn("select count(*) from course_lesson where type='video';");
        $c2 = $connection->fetchColumn("select count(*) from activity where mediaType='video';");
        $c3 = $connection->fetchColumn('select count(*) from activity_video;');
        $c4 = $connection->fetchColumn("select count(*) from course_task where activityId in (select id from activity where mediaType = 'video');");
        if ($c1 == $c2 && $c2 == $c3 && $c3 == $c4) {
            $output->writeln("<info> 视频 数据验证通过，{$c1} {$c2} {$c3} {$c4}.</info>");
        } else {
            $output->writeln("<error> 视频 数据验证不通过，{$c1} {$c2} {$c3} {$c4}.</error>");
        }

        // 音频：
        $c1 = $connection->fetchColumn("select count(*) from course_lesson where type='audio';");
        $c2 = $connection->fetchColumn("select count(*) from activity where mediaType='audio';");
        $c3 = $connection->fetchColumn('select count(*) from activity_audio;');
        $c4 = $connection->fetchColumn("select count(*) from course_task where activityId in (select id from activity where mediaType = 'audio');");
        if ($c1 == $c2 && $c2 == $c3 && $c3 == $c4) {
            $output->writeln("<info> 音频 数据验证通过，{$c1} {$c2} {$c3} {$c4}.</info>");
        } else {
            $output->writeln("<error> 音频 数据验证不通过，{$c1} {$c2} {$c3} {$c4}.</error>");
        }

        // ppt：
        $c1 = $connection->fetchColumn("select count(*) from course_lesson where type='ppt';");
        $c2 = $connection->fetchColumn("select count(*) from activity where mediaType='ppt';");
        $c3 = $connection->fetchColumn('select count(*) from activity_ppt;');
        $c4 = $connection->fetchColumn("select count(*) from course_task where activityId in (select id from activity where mediaType = 'ppt');");
        if ($c1 == $c2 && $c2 == $c3 && $c3 == $c4) {
            $output->writeln("<info> ppt 数据验证通过，{$c1} {$c2} {$c3} {$c4}.</info>");
        } else {
            $output->writeln("<error> ppt 数据验证不通过，{$c1} {$c2} {$c3} {$c4}.</error>");
        }

        // live：
        $c1 = $connection->fetchColumn("select count(*) from course_lesson where type='live';");
        $c2 = $connection->fetchColumn("select count(*) from activity where mediaType='live';");
        $c3 = $connection->fetchColumn('select count(*) from activity_live;');
        $c4 = $connection->fetchColumn("select count(*) from course_task where activityId in (select id from activity where mediaType = 'live');");
        if ($c1 == $c2 && $c2 == $c3 && $c3 == $c4) {
            $output->writeln("<info> live 数据验证通过，{$c1} {$c2} {$c3} {$c4}.</info>");
        } else {
            $output->writeln("<error> live 数据验证不通过，{$c1} {$c2} {$c3} {$c4}.</error>");
        }

        // document：
        $c1 = $connection->fetchColumn("select count(*) from course_lesson where type='document';");
        $c2 = $connection->fetchColumn("select count(*) from activity where mediaType='doc';");
        $c3 = $connection->fetchColumn('select count(*) from activity_doc;');
        $c4 = $connection->fetchColumn("select count(*) from course_task where activityId in (select id from activity where mediaType = 'doc');");
        if ($c1 == $c2 && $c2 == $c3 && $c3 == $c4) {
            $output->writeln("<info> document 数据验证通过，{$c1} {$c2} {$c3} {$c4}.</info>");
        } else {
            $output->writeln("<error> document 数据验证不通过，{$c1} {$c2} {$c3} {$c4}.</error>");
        }

        // text:
        $c1 = $connection->fetchColumn("select count(*) from course_lesson where type='text';");
        $c2 = $connection->fetchColumn("select count(*) from activity where mediaType='text';");
        $c3 = $connection->fetchColumn('select count(*) from activity_text;');
        $c4 = $connection->fetchColumn("select count(*) from course_task where activityId in (select id from activity where mediaType = 'text');");
        if ($c1 == $c2 && $c2 == $c3 && $c3 == $c4) {
            $output->writeln("<info> text 数据验证通过，{$c1} {$c2} {$c3} {$c4}.</info>");
        } else {
            $output->writeln("<error> text 数据验证不通过，{$c1} {$c2} {$c3} {$c4}.</error>");
        }

        // flash:
        $c1 = $connection->fetchColumn("select count(*) from course_lesson where type='flash';");
        $c2 = $connection->fetchColumn("select count(*) from activity where mediaType='flash';");
        $c3 = $connection->fetchColumn('select count(*) from activity_flash;');
        $c4 = $connection->fetchColumn("select count(*) from course_task where activityId in (select id from activity where mediaType = 'flash');");
        if ($c1 == $c2 && $c2 == $c3 && $c3 == $c4) {
            $output->writeln("<info> flash 数据验证通过，{$c1} {$c2} {$c3} {$c4}.</info>");
        } else {
            $output->writeln("<error> flash 数据验证不通过，{$c1} {$c2} {$c3} {$c4}.</error>");
        }

        // download:
        $c1 = $connection->fetchColumn(" SELECT count(*) FROM (SELECT max(lessonId) FROM course_material WHERE source = 'coursematerial' AND  `lessonId` >0 AND `lessonId`  IN  (SELECT migrateLessonId FROM `course_task`)   GROUP BY lessonId ) cm;");
        $c2 = $connection->fetchColumn('SELECT count(*) FROM `activity_download` WHERE migrateLessonId IN (SELECT migrateLessonId FROM `course_task`)');
        $c3 = $connection->fetchColumn("select count(*) from activity where mediaType='download' and migrateLessonId IN (SELECT migrateLessonId FROM `course_task`) ;");
        $c4 = $connection->fetchColumn("select count(*) from course_task where activityId in (select id from activity where mediaType = 'download');");
        $c5 = $connection->fetchColumn("SELECT count(*) FROM activity WHERE mediaId IN (SELECT id FROM activity_download ) AND  migrateLessonId IN (SELECT migrateLessonId FROM `course_task`)  and mediaType = 'download';");

        if ($c1 == $c2 && $c2 == $c3 && $c3 == $c4 && $c4 == $c5) {
            $output->writeln("<info> download 数据验证通过，{$c1} {$c2} {$c3} {$c4} {$c5}.</info>");
        } else {
            $output->writeln("<error> download 数据验证不通过，{$c1} {$c2} {$c3} {$c4} {$c5}.</error>");
        }

        // 课时：
        $c1 = $connection->fetchColumn('select count(*) from course_lesson;');
        $c2 = $connection->fetchColumn("select count(*) from course_chapter where type = 'lesson' and migrateLessonId <>0;");
        if ($c1 == $c2) {
            $output->writeln("<info> 课时 数据验证通过，{$c1} {$c2}.</info>");
        } else {
            $output->writeln("<error> 课时 数据验证不通过，{$c1} {$c2}.</error>");
        }

        // 除去作业、练习、下载的任务：
        $c1 = $connection->fetchColumn('select count(*) from course_lesson;');
        $c2 = $connection->fetchColumn("select count(*) from activity where mediaType not in ('homework', 'exercise', 'download');");
        $c3 = $connection->fetchColumn("select count(*) from course_task where activityId in (select id from activity where mediaType not in ('homework', 'exercise', 'download'));");
        if ($c1 == $c2 && $c2 == $c3) {
            $output->writeln("<info> 除去作业、练习、下载的任务 数据验证通过，{$c1} {$c2} {$c3}.</info>");
        } else {
            $output->writeln("<error> 除去作业、练习、下载的任务 数据验证不通过，{$c1} {$c2} {$c3}.</error>");
        }

        // task、activity汇总校验：
        $c1 = $connection->fetchColumn('select count(*) from course_task;');
        $c2 = $connection->fetchColumn('select count(*) from activity WHERE `migrateLessonId` IN (SELECT migrateLessonId FROM `course_task`);');
        if ($c1 == $c2) {
            $output->writeln("<info> task、activity汇总校验 数据验证通过，{$c1} {$c2}.</info>");
        } else {
            $output->writeln("<error> task、activity汇总校验 数据验证不通过，{$c1} {$c2}.</error>");
        }

        // testpaper_result校验
        $c1 = $connection->fetchColumn('select count(*) from testpaper_result;');
        $c2 = $connection->fetchColumn("select count(*) from testpaper_result_v8 where type='testpaper';");
        if ($c1 == $c2) {
            $output->writeln("<info> testpaper_result校验 数据验证通过，{$c1} {$c2}.</info>");
        } else {
            $output->writeln("<error> testpaper_result校验 数据验证不通过，{$c1} {$c2}.</error>");
        }

        // testpaper_item_result校验
        $c1 = $connection->fetchColumn('select count(*) from testpaper_item_result;');
        $c2 = $connection->fetchColumn("select count(*) from testpaper_item_result_v8 where type='testpaper';");
        if ($c1 == $c2) {
            $output->writeln("<info> testpaper_item_result校验 数据验证通过，{$c1} {$c2}.</info>");
        } else {
            $output->writeln("<error> testpaper_item_result校验 数据验证不通过，{$c1} {$c2}.</error>");
        }

        // homework_result校验
        $c1 = $connection->fetchColumn('select count(*) from homework_result;');
        $c2 = $connection->fetchColumn("select count(*) from testpaper_result_v8 where type='homework';");
        if ($c1 == $c2) {
            $output->writeln("<info> homework_result校验 数据验证通过，{$c1} {$c2}.</info>");
        } else {
            $output->writeln("<error> homework_result校验 数据验证不通过，{$c1} {$c2}.</error>");
        }

        // homework_item_result校验
        $c1 = $connection->fetchColumn('select count(*) from homework_item_result;');
        $c2 = $connection->fetchColumn("select count(*) from testpaper_item_result_v8 where type='homework';");
        if ($c1 == $c2) {
            $output->writeln("<info> homework_item_result校验 数据验证通过，{$c1} {$c2}.</info>");
        } else {
            $output->writeln("<error> homework_item_result校验 数据验证不通过，{$c1} {$c2}.</error>");
        }

        // exercise_result校验
        $c1 = $connection->fetchColumn('select count(*) from exercise_result;');
        $c2 = $connection->fetchColumn("select count(*) from testpaper_result_v8 where type='exercise';");
        if ($c1 == $c2) {
            $output->writeln("<info> exercise_result校验 数据验证通过，{$c1} {$c2}.</info>");
        } else {
            $output->writeln("<error> exercise_result校验 数据验证不通过，{$c1} {$c2}.</error>");
        }

        // exercise_item_result校验
        $c1 = $connection->fetchColumn('select count(*) from exercise_item_result;');
        $c2 = $connection->fetchColumn("select count(*) from testpaper_item_result_v8 where type='exercise';");
        if ($c1 == $c2) {
            $output->writeln("<info> exercise_item_result校验 数据验证通过，{$c1} {$c2}.</info>");
        } else {
            $output->writeln("<error> exercise_item_result校验 数据验证不通过，{$c1} {$c2}.</error>");
        }
    }
}
