<?php

namespace Biz\Question\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class QuestionEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'question.create' => 'onQuestionCreate',
            'question.update' => 'onQuestionUpdate',
            'question.delete' => 'onQuestionDelete',
        );
    }

    public function onQuestionCreate(Event $event)
    {
        $question = $event->getSubject();
        //$argument   = $event->getArgument('argument');
        //$attachment = $argument['attachment'];

        //do something
    }

    public function onQuestionUpdate(Event $event)
    {
        $question = $event->getSubject();
        //$argument   = $event->getArgument('argument');
        //$attachment = $argument['fields']['attachment'];

        //do something
    }

    public function onQuestionDelete(Event $event)
    {
        $question = $event->getSubject();

        //do something
    }

    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }
}
