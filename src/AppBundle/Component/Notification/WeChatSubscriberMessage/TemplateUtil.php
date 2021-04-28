<?php

namespace AppBundle\Component\Notification\WeChatSubscriberMessage;

class TemplateUtil
{
    const TEMPLATE_ASK_QUESTION = 'subscribeAskQuestion';

    const TEMPLATE_LIVE_OPEN = 'subscribeLiveOpen';

    const TEMPLATE_HOMEWORK_RESULT = 'subscribeHomeworkResult';

    const TEMPLATE_EXAM_RESULT = 'subscribeExamResult';

    const TEMPLATE_COURSE_UPDATE = 'subscribeCourseUpdate';

    public static function templates()
    {
        $templates = [
            self::TEMPLATE_ASK_QUESTION => [
            ],
            self::TEMPLATE_LIVE_OPEN => [
            ],
            self::TEMPLATE_HOMEWORK_RESULT => [
            ],
            self::TEMPLATE_EXAM_RESULT => [
            ],
            self::TEMPLATE_COURSE_UPDATE => [
            ],
        ];

        return $templates;
    }
}
