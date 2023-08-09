<?php

namespace Biz\ItemBankExercise\Job;

use AppBundle\Common\ArrayToolkit;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\Role\Util\PermissionBuilder;
use Biz\System\Service\LogService;
use Biz\User\CurrentUser;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

class DeleteNotExistItemBankExerciseJob extends AbstractJob
{
    const LIMIT = 500;

    public function execute()
    {
        $this->setCurrentUser();

        $excludeIds = $this->args['excludeIds'] ?? [];

        $itemBankExercises = $this->getItemBankExerciseService()->search(['excludeIds' => $excludeIds], ['id' => 'desc'], 0, self::LIMIT, ['id', 'questionBankId', 'status']);
        if (empty($itemBankExercises)) {
            return;
        }
        $excludeIds = array_merge($excludeIds, array_column($itemBankExercises, 'id'));

        $exerciseQuestionBankIds = array_column($itemBankExercises, 'questionBankId');
        $questionBanks = $this->getQuestionBankService()->searchQuestionBanks(['ids' => $exerciseQuestionBankIds], ['id' => 'desc'], 0, count($exerciseQuestionBankIds), ['id']);
        $questionBankIds = array_column($questionBanks, 'id');

        $deletedQuestionBankIds = array_diff($exerciseQuestionBankIds, $questionBankIds);

        $itemBankExercises = ArrayToolkit::index($itemBankExercises, 'questionBankId');
        foreach ($deletedQuestionBankIds as $deletedQuestionBankId) {
            $toDeleteItemBankExercise = $itemBankExercises[$deletedQuestionBankId];

            if ('closed' != $toDeleteItemBankExercise['status']) {
                $this->getItemBankExerciseService()->closeExercise($toDeleteItemBankExercise['id']);
                $this->getLogService()->info('item_bank_exercise', 'close_exercise', "关闭题库练习{$toDeleteItemBankExercise['id']}，questionBankId为{$deletedQuestionBankId}");
            }

            $this->getItemBankExerciseService()->deleteExercise($toDeleteItemBankExercise['id']);
            $this->getLogService()->info('item_bank_exercise', 'delete_exercise', "删除题库练习{$toDeleteItemBankExercise['id']}，questionBankId为{$deletedQuestionBankId}");
        }

        $this->getSchedulerService()->register([
            'name' => 'DeleteNotExistItemBankExerciseJob',
            'expression' => time(),
            'class' => 'Biz\ItemBankExercise\Job\DeleteNotExistItemBankExerciseJob',
            'misfire_policy' => 'executing',
            'misfire_threshold' => 0,
            'args' => ['excludeIds' => $excludeIds],
        ]);
    }

    protected function setCurrentUser()
    {
        $systemUser = $this->getUserService()->getUserByType('system');
        $systemUser['currentIp'] = '127.0.0.1';
        $currentUser = new CurrentUser();
        $currentUser->fromArray($systemUser);
        $currentUser->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));
        $this->biz['user'] = $currentUser;
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
     * @return SchedulerService
     */
    private function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }
}
