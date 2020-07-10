<?php

namespace Biz\ItemBankExercise\Event;

use AppBundle\Common\MathToolkit;
use Biz\ItemBankExercise\Dao\ExerciseDao;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\OrderFacade\Service\OrderFacadeService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Order\Service\OrderService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExerciseMemberEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'exercise.join' => 'onExerciseJoin',
            'exercise.quit' => 'onExerciseQuit',
        );
    }

    public function onExerciseJoin(Event $event)
    {
        $this->countStudentMember($event);
        $this->countIncome($event);
    }

    public function onExerciseQuit(Event $event)
    {
        $this->countStudentMember($event);
        $this->countIncome($event);
    }

    private function countStudentMember(Event $event)
    {
        $exercise = $event->getSubject();
        $member = $event->getArgument('member');

        if ('student' == $member['role']) {
            $this->getExerciseService()->updateExerciseStatistics($exercise['id'], ['studentNum']);
        }
    }

    private function countIncome(Event $event)
    {
        $exercise = $event->getSubject();

        $conditions = array(
            'exerciseId' => $exercise['id'],
            'statuses' => array('paid', 'success', 'finished'),
        );

        $income = $this->getOrderFacadeService()->sumOrderItemPayAmount($conditions);
        $income = MathToolkit::simple($income, 0.01);

        $this->getExerciseDao()->update($exercise['id'], ['income' => $income]);
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }

    /**
     * @return OrderFacadeService
     */
    protected function getOrderFacadeService()
    {
        return $this->getBiz()->service('OrderFacade:OrderFacadeService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return ExerciseMemberService
     */
    protected function getExerciseMemberService()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseMemberService');
    }

    /**
     * @return ExerciseDao
     */
    protected function getExerciseDao()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseDao');
    }
}
