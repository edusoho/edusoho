<?php

namespace AppBundle\Command;

use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExamTimingHistoricalDataProcessingCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('exam:historical_data_processing');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->LimitedTimeAmountZero($output);
        $this->LimitedTimeGreaterThanZero($output);
    }

    /**
     * 考试时长等于0
     */
    protected function LimitedTimeAmountZero(OutputInterface $output) {
        $answerScenes = $this->getAnswerSceneService()->search(['limited_time' => 0], [], 0, PHP_INT_MAX, ['id', 'exam_mode', 'enable_facein', 'name', 'limited_time']);
        foreach ($answerScenes as $answerScene) {
            $answerScene = $this->getAnswerSceneService()->update($answerScene['id'], ['exam_mode'=> 1, 'enable_facein'=> 0, 'name' => $answerScene['name']]);
            $answerRecords = $this->getAnswerRecordService()->search(['answer_scene_id' => $answerScene['id']], [], 0, PHP_INT_MAX, ['id', 'exam_mode', 'limited_time']);
            foreach ($answerRecords as $answerRecord) {
                $this->getAnswerRecordService()->update($answerRecord['id'], ['exam_mode'=> $answerScene['exam_mode'], 'limited_time' => $answerScene['limited_time']]);
            }
        }

        $output->writeln("<info>执行成功</info>");
    }

    /**
     * 考试时长大于0
     */
    protected function LimitedTimeGreaterThanZero(OutputInterface $output) {
        $answerScenes = $this->getAnswerSceneService()->search(['limited_times' => 0], [], 0, PHP_INT_MAX, ['id', 'exam_mode', 'enable_facein', 'name', 'limited_time']);
        foreach ($answerScenes as $answerScene) {
            $answerScene = $this->getAnswerSceneService()->update($answerScene['id'], ['exam_mode'=> 0, 'name' => $answerScene['name']]);
            $answerRecords = $this->getAnswerRecordService()->search(['answer_scene_id' => $answerScene['id']], [], 0, PHP_INT_MAX, ['id', 'exam_mode', 'limited_time']);
            foreach ($answerRecords as $answerRecord) {
                $this->getAnswerRecordService()->update($answerRecord['id'], ['exam_mode'=> $answerScene['exam_mode'], 'limited_time' => $answerScene['limited_time']]);
            }
        }
        $output->writeln("<info>执行成功</info>");
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->createService('ItemBank:Answer:AnswerRecordService');
    }
}
