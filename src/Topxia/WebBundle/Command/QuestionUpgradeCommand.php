<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Topxia\Service\User\CurrentUser;
use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class QuestionUpgradeCommand extends BaseCommand
{

    protected function configure()
    {
        $this->setName ( 'topxia:question-upgrade' );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = $this->getContainer()->get('database_connection');

        $connection->beginTransaction();
        try{
            $oldQuestions = $connection->fetchAll("select * from question;");

            foreach ($oldQuestions as $oldQuestion) {

                $newQuestion = array();
                if ($oldQuestion['targetType'] == 'course'){
                    $newQuestion = array(
                        'target' => $oldQuestion['targetType']."-".$oldQuestion['targetId']
                    );
                } elseif ($oldQuestion['targetType'] == 'lesson'){

                    $lesson = $connection->fetchAssoc("select * from course_lesson where id = ? ;", array($oldQuestion['targetId']));
                    if ($lesson) {
                        $newQuestion = array(
                            'target' => 'course-'.$lesson['courseId']."/".$oldQuestion['targetType']."-".$oldQuestion['targetId']
                        );
                    }
                }

                if ($newQuestion) {
                    $connection->update('question', $newQuestion, array('id'=>$oldQuestion['id']));
                }
            }

            $oldQuestion_categorys = $connection->fetchAll("select * from question_category;");

            foreach ($oldQuestion_categorys as $oldQuestion_category) {

                $newQuestion_category = array(
                    'target' => $oldQuestion_category['targetType']."-".$oldQuestion_category['targetId']
                );

                $connection->update('question_category', $newQuestion_category, array('id'=>$oldQuestion_category['id']));
            }

            $oldQuestions = $connection->fetchAll("select * from question_favorite;");

            foreach ($oldQuestions as $oldQuestion) {

                $newQuestion = array(
                    'target' => $oldQuestion['targetType']."-".$oldQuestion['targetId']
                );
                

                $connection->update('question_favorite', $newQuestion, array('id'=>$oldQuestion['id']));
            }


            $oldTestpapers = $connection->fetchAll("select * from testpaper;");

            foreach ($oldTestpapers as $oldTestpaper) {

                $newTestpaper = array(
                    'target' => $oldTestpaper['targetType']."-".$oldTestpaper['targetId']
                );

                $connection->update('testpaper', $newTestpaper, array('id'=>$oldTestpaper['id']));
            }


            $oldTestpaperResults = $connection->fetchAll("select * from testpaper_result;");

            foreach ($oldTestpaperResults as $oldTestpaperResult) {

                $newTestpaperResult = array();
                if ($oldTestpaperResult['targetType'] == 'course'){
                    $newTestpaperResult = array(
                        'target' => $oldTestpaperResult['targetType']."-".$oldTestpaperResult['targetId']
                    );
                } elseif ($oldTestpaperResult['targetType'] == 'lesson') {

                    $lesson = $connection->fetchAssoc("select * from course_lesson where id = ? ;", array($oldTestpaperResult['targetId']));

                    if ($lesson) {
                        $newTestpaperResult = array(
                            'target' => 'course-'.$lesson['courseId']."/".$oldTestpaperResult['targetType']."-".$oldTestpaperResult['targetId']
                        );
                    }
                }

                if ($newTestpaperResult) {
                    $connection->update('testpaper_result', $newTestpaperResult, array('id'=>$oldTestpaperResult['id']));
                }

            }

            $connection->commit();

        }catch(\Exception $e){
            $connection->rollback();
            throw $e;
        }

    }

}