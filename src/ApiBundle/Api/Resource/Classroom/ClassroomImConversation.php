<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\ClassroomException;
use Biz\IM\ConversationException;
use ApiBundle\Api\Annotation\ResponseFilter;

class ClassroomImConversation extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\Conversation\ConversationFilter", mode="public")
     */
    public function add(ApiRequest $request, $classroomId)
    {
        $canLearn = $this->getClassroomService()->canLearnClassroom($classroomId);

        if ('success' != $canLearn['code']) {
            throw ClassroomException::FORBIDDEN_TAKE_CLASSROOM();
        }

        return $this->entryClassroomConversation($classroomId);
    }

    protected function entryClassroomConversation($classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        $conversation = $this->getConversationService()->getConversationByTarget($classroomId, 'classroom');

        $user = $this->getCurrentUser();

        try {
            if ($conversation) {
                $convMember = $this->getConversationService()->getMemberByConvNoAndUserId($conversation['no'], $user['id']);

                if (empty($convMember)) {
                    if ($this->getConversationService()->isImMemberFull($conversation['no'], 500)) {
                        throw ConversationException::CONVERSATION_IS_FULL();
                    }

                    $this->getConversationService()->joinConversation($conversation['no'], $user['id']);
                }
            } else {
                $conversation = $this->getConversationService()->createConversation($classroom['title'], 'classroom', $classroom['id'], array($user));
            }
        } catch (\Exception $e) {
            throw ConversationException::JOIN_FAILED();
        }

        return array(
            'convNo' => $conversation['no'],
            'classroom' => $classroom,
        );
    }

    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    protected function getConversationService()
    {
        return $this->service('IM:ConversationService');
    }
}
