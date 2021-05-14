<?php

namespace AppBundle\Command;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Topxia\Service\Common\ServiceKernel;

class FixTestpaperWithAiCommand extends BaseCommand
{
    private $logger;

    protected function configure()
    {
        $this->setName('fix:testpaper-with-ai')
            ->addArgument('userId', InputArgument::REQUIRED, '用户id')
            ->addArgument('recordId', InputArgument::REQUIRED, '答题记录id');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();

        $userId = $input->getArgument('userId');
        $recordId = $input->getArgument('recordId');

        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            var_dump('用户不存在');

            return;
        }

        $record = $this->getAnswerRecordService()->get($recordId);
        if (empty($record)) {
            var_dump('答题记录不存在');

            return;
        }

        if ($user['id'] != $record['user_id']) {
            var_dump('--------用户答题记录不存在--------');

            return;
        }

        if ('finished' != $record['status']) {
            var_dump('答题未完成');

            return;
        }

        if ($record['used_time'] > 0) {
            var_dump('已正确记录答题时间');

            return;
        }

        $report = $this->getAnswerReportService()->search(
            [
                'user_id' => $userId,
                'answer_scene_id' => $record['answer_scene_id'],
                'assessment_id' => $record['assessment_id'],
            ],
            [],
            0,
            PHP_INT_MAX
        );

        if (empty($report) || count($report) > 1) {
            var_dump('答题报告不存在或次数大于1');

            return;
        }

        $questionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($recordId);
        if (empty($questionReports)) {
            var_dump('答题问题记录不存在');

            return;
        }

        $this->getLogger()->info('---------------- start ----------------');
        $this->getLogger()->info('----------------'.$userId.' and '.$recordId.'----------------');
        $this->getLogger()->info('---------------- record ----------------');
        $this->getLogger()->info('record: '.json_encode($record));
        $this->getLogger()->info('---------------- report ----------------');
        $this->getLogger()->info('report: '.json_encode($report));
        $this->getLogger()->info('---------------- question reports ----------------');
        foreach ($questionReports as $key => $questionReport) {
            $this->getLogger()->info('question report '.$key.' : '.json_encode($questionReport));
        }

        $this->getLogger()->info('---------------- end ----------------');

        try {
            $this->getBiz()['db']->beginTransaction();

            $sql1 = 'DELETE FROM `biz_answer_record` WHERE id = '.(int) $record['id'].' LIMIT 1';
            $sql2 = 'DELETE FROM `biz_answer_report` WHERE answer_record_id = '.(int) $record['id'].' LIMIT 1';
            $sql3 = 'DELETE FROM `biz_answer_question_report` WHERE answer_record_id = '.(int) $record['id'].' LIMIT '.count($questionReports);

            $this->getBiz()['db']->executeUpdate($sql1);
            $this->getBiz()['db']->executeUpdate($sql2);
            $this->getBiz()['db']->executeUpdate($sql3);

            $this->getBiz()['db']->commit();
            var_dump('删除成功');

            return;
        } catch (\Exception $e) {
            var_dump('删除失败');
            var_dump($e->getMessage());
            $this->getBiz()['db']->rollback();
            throw $e;
        }
    }

    protected function getLogger($name = 'testpaper-with-ai')
    {
        if ($this->logger) {
            return $this->logger;
        }

        $this->logger = new Logger($name);

        $biz = $this->getBiz();
        $this->logger->pushHandler(new StreamHandler($biz['log_directory'].'/testpaper-with-ai.log', Logger::DEBUG));

        return $this->logger;
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return ServiceKernel::instance()->createService('ItemBank:Answer:AnswerRecordService');
    }

    protected function getAnswerReportService()
    {
        return ServiceKernel::instance()->createService('ItemBank:Answer:AnswerReportService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService
     */
    protected function getAnswerQuestionReportService()
    {
        return ServiceKernel::instance()->createService('ItemBank:Answer:AnswerQuestionReportService');
    }
}
