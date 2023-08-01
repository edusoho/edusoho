<?php

namespace Biz\ItemBankExercise\Job;

use AppBundle\Common\ArrayToolkit;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\System\Service\LogService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class DeleteNotExitItemBankExerciseJob extends AbstractJob
{
    const LiMIT = 500;

    public function execute()
    {
        $start = empty($this->args['start']) ? 0 : $this->args['start'];

        $questionBanks = $this->getQuestionBankService()->searchQuestionBanks([], ['id' => 'desc'], $start, self::LiMIT);
        if (empty($questionBanks)) {
            return;
        }
        $questionBankIds = array_column($questionBanks, 'id');

        $itemBankExercises = $this->getItemBankExerciseService()->search([], [], $start, self::LiMIT);
        if (empty($itemBankExercises)) {
            return;
        }
        $itemBankExerciseIds = array_column($itemBankExercises, 'questionBankId');

        $diffs = array_diff($itemBankExerciseIds, $questionBankIds);

        $itemBankExercises = ArrayToolkit::index($itemBankExercises, 'questionBankId');
        foreach ($diffs as $diff) {
            $itemBankExerciseId = $itemBankExercises[$diff]['id'];
            $questionBankId = $diff;

            if ('closed' != $itemBankExercises[$diff]['status']) {
                $this->getItemBankExerciseService()->closeExercise($itemBankExerciseId);
                $this->getLogService()->info('item_bank_exercise', 'close_exercise', "关闭题库练习{$itemBankExerciseId}，questionBankId为{$questionBankId}");
            }

            $this->getItemBankExerciseService()->deleteExercise($itemBankExerciseId);
            $this->getLogService()->info('item_bank_exercise', 'delete_exercise', "删除题库练习{$itemBankExerciseId}，questionBankId为{$questionBankId}");
        }

        $this->getSchedulerService()->deleteJobByName('DeleteNotExitItemBankExerciseJob');

        $this->getSchedulerService()->register([
            'name' => 'DeleteNotExitItemBankExerciseJob',
            'expression' => time(),
            'class' => 'Biz\ItemBankExercise\Job\DeleteNotExitItemBankExerciseJob',
            'misfire_policy' => 'executing',
            'misfire_threshold' => 0,
            'args' => ['start' => $start + self::LiMIT],
        ]);
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->biz->service('QuestionBank:QuestionBankService');
    }

    /**
     * @return ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    /**
     * @return \Codeages\Biz\Framework\Scheduler\Service\SchedulerService
     */
    private function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }
}
