<?php
/**
 * Created by PhpStorm.
 * User: malianbo
 * Date: 17/2/5
 * Time: 16:34
 */

namespace Biz\Question\Event;

use Biz\Question\Dao\QuestionDao;
use Codeages\Biz\Framework\Event\Event;
use Biz\Course\Event\CourseSyncSubscriber;

class QuestionSyncSubscriber extends CourseSyncSubscriber
{
    public static function getSubscribedEvents()
    {
        return array(
            'question.create'        => 'onQuestionCreate',
            'question.update'        => 'onQuestionUpdate',
            'question.delete'        => 'onQuestionDelete',
        );
    }

    public function onQuestionCreate(Event $event)
    {

    }

    public function onQuestionUpdate(Event $event)
    {

    }

    public function onQuestionDelete(Event $event)
    {

    }

    /**
     * @return QuestionDao
     */
    protected function getQuestionDao()
    {
        return $this->getBiz()->dao('Question:QuestionDao');
    }
}