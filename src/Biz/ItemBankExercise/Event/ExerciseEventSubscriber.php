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
        return array(
            'exercise.set' => 'onExerciseUpdate',
        );
    }

    public function onExerciseUpdate(Event $event)
    {
        $exercise = $event->getSubject();

        if (!isset($courseSet['categoryId'])) {
            return;
        }

        $this->getExerciseService()->updateCategoryByExerciseId($exercise['id'], $exercise['categoryId']);
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseService');
    }
}
