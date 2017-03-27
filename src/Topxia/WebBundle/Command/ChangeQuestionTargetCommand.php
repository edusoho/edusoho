<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Topxia\Common\Paginator;
use Topxia\Service\Common\ServiceKernel;


class ChangeQuestionTargetCommand extends BaseCommand
{
    protected $num = 1000;

    protected function configure()
    {
        $this->setName('unit:change-question-target');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>变更试题从属关系...</info>');

        $sourceQuestionCount = $this->searchSourceQuestionCount();
        for ($i=0; $i < $sourceQuestionCount/$this->num; $i++) { 
            $sourceQuestions = $this->getQuestionService()->searchQuestions(
                array('copyId' => 0), 
                array('createdTime', 'DESC'), 
                0, 
                $this->num*($i + 1)
            );

            foreach ($sourceQuestions as $sourceQuestion) {
                $questionTarget = explode('/', $sourceQuestion['target']);
                $num = count($questionTarget);
                //只有课时题目做处理
                if ($num > 1) {
                    $questionLessonTarget = explode('-', $questionTarget[1]);
                    $lessonId = $questionLessonTarget[1];
                    $lesson = $this->getLesson($lessonId);

                    if (empty($lesson)) {
                        $this->dealQuestionTarget($sourceQuestion);
                        $this->dealCopyQuestion($sourceQuestion);
                    }
                }
            }
        }
    }

    private function searchSourceQuestionCount()
    {
        $sql = "select count(*) from question where copyId = 0";
        $count = $this->db()->fetchAssoc($sql, array());
        return $count['count(*)'];
    }

    private function dealQuestionTarget($question)
    {
        $target = explode('/', $question['target']);
        return $this->getQuestionService()->updateQuestionTargetById($question['id'], array('target' => $target[0]));
    }

    private function dealCopyQuestion($sourceQuestion)
    {
        $copyQuestions = $this->findCopyQuestion($sourceQuestion);
        foreach ($copyQuestions as $copyQuestion) {
            $this->dealQuestionTarget($copyQuestion);
        }
    }

    private function getLesson($lessonId)
    {
        $sql = "select * from course_lesson where id = {$lessonId}";
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

    protected function getQuestionService()
    {
        return ServiceKernel::instance()->createService('Question.QuestionService');
    }
}
