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

        $oldQuestions = $connection->fetchAll("select * from course_quiz_item;");

        foreach ($oldQuestions as $oldQuestion) {
            $oldQuestion['answers'] = explode('|', $oldQuestion['answers']);
            $newQuestion = array(
                'type' => count($oldQuestion['answers']) > 1 ? 'choice' : 'single_choice',
                'stem' => $oldQuestion['description'],
                'score' => '2',
                'answer' => $oldQuestion['answers'],
                'analysis' => '',
                'metas' => array('choices' => json_decode($oldQuestion['choices'], true)),
                'categoryId' => 0,
                'difficulty' => 'normal',
                'targetId' => $oldQuestion['lessonId'],
                'targetType' => 'lesson',
                'parentId' => 0,
                'subCount' => 0,
                'finishedTimes' => 0,
                'passedTimes' => 0,
                'userId' => $oldQuestion['userId'],
                'updatedTime' => $oldQuestion['createdTime'],
                'createdTime' => $oldQuestion['createdTime'],
            );
            $newQuestion['metas'] = json_encode($newQuestion['metas']);
            $newQuestion['answer'] = json_encode($newQuestion['answer']);

            $connection->insert('question', $newQuestion);

        }


    }

}