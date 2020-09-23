<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Topxia\Service\Common\ServiceKernel;

class FixTestpaperWithAiCommand extends BaseCommand
{
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

        if ($record['status'] != 'finished') {
            var_dump('答题未完成');
            return;
        }

        if ($record['used_time'] > 0) {
            var_dump('已正确记录答题时间');
            return;
        }

        $questionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($recordId);
        if (empty($questionReports)) {
            var_dump('答题问题记录不存在');
            return;
        }

        try {
            $this->getBiz()['db']->beginTransaction();

            $sql1 = 'DELETE FROM `biz_answer_record` WHERE id = ' . (int)$record['id'] . ' LIMIT 1';
            $sql2 = 'DELETE FROM `biz_answer_report` WHERE answer_record_id = ' . (int)$record['id'] . ' LIMIT 1';
            $sql3 = 'DELETE FROM `biz_answer_question_report` WHERE answer_record_id = ' . (int)$record['id'] . ' LIMIT ' . count($questionReports);

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

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService
     */
    protected function getAnswerQuestionReportService()
    {
        return ServiceKernel::instance()->createService('ItemBank:Answer:AnswerQuestionReportService');
    }
}
