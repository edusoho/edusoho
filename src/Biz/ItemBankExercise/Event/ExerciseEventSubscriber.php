<?php

namespace Biz\ItemBankExercise\Event;

use Biz\ItemBankExercise\Service\ExerciseService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExerciseEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'questionBank.update' => 'onQuestionBankUpdate',
        ];
    }

    public function onQuestionBankUpdate(Event $event)
    {
        $questionBank = $event->getSubject();
        $exercise = $this->getExerciseService()->getByQuestionBankId($questionBank['id']);

        $this->getExerciseService()->update(
            $exercise['id'],
            [
                'categoryId' => $questionBank['categoryId'],
                'title' => $questionBank['name'],
            ]
        );
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseService');
    }
}
