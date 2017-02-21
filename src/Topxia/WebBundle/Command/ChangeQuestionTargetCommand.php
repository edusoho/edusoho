<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Topxia\Service\Common\ServiceKernel;


class ChangeQuestionTargetCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('unit:change-question-target');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>变更试题从属关系...</info>');
        $questions = $this->getQuestionService()->searchQuestions(array(), array('createTime', 'DESC'), 0, PHP_INT_MAX);
        foreach ($questions as $question) {
            $status = $this->filter($question);
            if ($status) {
                $this->changeQuestionTarget($question);
            }
        }

    }

    private function changeQuestionTarget($question)
    {
        $this->filterQuestion();
    }

    private function filter($question)
    {

    }

    protected function getQuestionService()
    {
        return ServiceKernel::instance()->createService('Question.QuestionService');
    }
}
