<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\CourseException;
use Biz\IM\ConversationException;
use ApiBundle\Api\Annotation\ResponseFilter;

class CourseImConversation extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\Conversation\ConversationFilter", mode="public")
     */
    public function add(ApiRequest $request, $courseId)
    {
        $canLearn = $this->getCourseService()->canLearnCourse($courseId);

        if ('success' != $canLearn['code']) {
            throw CourseException::FORBIDDEN_TAKE_COURSE();
        }

        return $this->entryCourseConversation($courseId);
    }

    protected function entryCourseConversation($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        $conversation = $this->getConversationService()->getConversationByTarget($courseId, 'course');

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
                $conversation = $this->getConversationService()->createConversation($course['title'], 'course', $course['id'], array($user));
            }
        } catch (\Exception $e) {
            throw ConversationException::JOIN_FAILED();
        }

        return array(
            'convNo' => $conversation['no'],
            'course' => $course,
        );
    }

    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    protected function getConversationService()
    {
        return $this->service('IM:ConversationService');
    }
}
