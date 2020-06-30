<?php

namespace Biz\ItemBankExercise\Event;

use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExerciseEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'exercise.update' => 'onExerciseUpdate',
        ];
    }

    public function onExerciseUpdate(Event $event)
    {
        $exercise = $event->getSubject();
        $categoryId = $event->getArgument('categoryId');
        if (!isset($categoryId)) {
            return;
        }

        $this->getExerciseService()->updateCategoryByExerciseId($exercise['id'], $categoryId);
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerSceneService');
    }
}
