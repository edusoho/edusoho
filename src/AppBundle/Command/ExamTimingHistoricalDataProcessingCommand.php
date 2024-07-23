<?php

namespace AppBundle\Command;

use Biz\Activity\Service\ActivityService;
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
        $answerRecordData= [];
        $activitys = $this->getActivityService()->search(['mediaType'=>'testpaper'], [], 0, PHP_INT_MAX, ['id', 'mediaId']);
        $activityTestpapers = $this->getTestpaperActivityService()->findActivitiesByIds(array_column($activitys,'mediaId'));

        $answerScenes = $this->getAnswerSceneService()->search(['limited_time' => 0, 'ids'=>array_column($activityTestpapers, 'answerSceneId')], [], 0, PHP_INT_MAX, ['id', 'exam_mode', 'enable_facein', 'name', 'limited_time']);
        foreach ($answerScenes as $answerScene) {
            $answerScene = $this->getAnswerSceneService()->update($answerScene['id'], ['exam_mode'=> 1, 'enable_facein'=> 0, 'name' => $answerScene['name']]);
            $answerRecords = $this->getAnswerRecordService()->search(['answer_scene_id' => $answerScene['id']], [], 0, PHP_INT_MAX, ['id', 'exam_mode', 'limited_time']);
            foreach ($answerRecords as $answerRecord) {
                $answerRecordData[$answerRecord['id']] = [
                    'exam_mode' => $answerScene['exam_mode'],
                    'limited_time' => $answerScene['limited_time'],
                ];
            }
        }

        $this->getAnswerRecordDao()->batchUpdate(array_keys($answerRecordData),array_values($answerRecordData));

        $output->writeln("<info>执行成功</info>");
    }

    /**
     * 考试时长大于0
     */
    protected function LimitedTimeGreaterThanZero(OutputInterface $output) {
        $answerRecordData= [];
        $activitys = $this->getActivityService()->search(['mediaType'=>'testpaper'], [], 0, PHP_INT_MAX, ['id', 'mediaId']);
        $activityTestpapers = $this->getTestpaperActivityService()->findActivitiesByIds(array_column($activitys,'mediaId'));

        $answerScenes = $this->getAnswerSceneService()->search(['limited_times' => 0, 'ids'=>array_column($activityTestpapers, 'answerSceneId')], [], 0, PHP_INT_MAX, ['id', 'exam_mode', 'enable_facein', 'name', 'limited_time']);
        foreach ($answerScenes as $answerScene) {
            $answerScene = $this->getAnswerSceneService()->update($answerScene['id'], ['exam_mode'=> 0, 'name' => $answerScene['name']]);
            $answerRecords = $this->getAnswerRecordService()->search(['answer_scene_id' => $answerScene['id']], [], 0, PHP_INT_MAX, ['id', 'exam_mode', 'limited_time']);
            foreach ($answerRecords as $answerRecord) {
                $answerRecordData[$answerRecord['id']] = [
                    'exam_mode' => $answerScene['exam_mode'],
                    'limited_time' => $answerScene['limited_time'],
                ];
            }
        }

        $this->getAnswerRecordDao()->batchUpdate(array_keys($answerRecordData),array_values($answerRecordData));

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


    protected function getTestpaperActivityService()
    {
        return $this->createService('Activity:TestpaperActivityService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    protected function getAnswerRecordDao()
    {
        return $this->getBiz()->dao('ItemBank:Answer:AnswerRecordDao');
    }
}

