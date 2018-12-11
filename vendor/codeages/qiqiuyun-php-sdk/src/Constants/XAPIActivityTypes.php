<?php

namespace QiQiuYun\SDK\Constants;

final class XAPIActivityTypes
{
    const APPLICATION = 'application';

    const AUDIO = 'audio';

    const CLASS_ONLINE = 'class-online';

    const COURSE = 'course';

    const ONLINE_DISCUSSION = 'online-discussion';

    const DOCUMENT = 'document';

    const EXERCISE = 'exercise';

    const HOMEWORK = 'homework';

    const INTERACTION = 'interaction';

    const LIVE = 'live';

    const MESSAGE = 'message';

    const QUESTION = 'question';

    const TEST_PAPER = 'testpaper';

    const VIDEO = 'video';

    const SEARCH_ENGINE = 'search-engine';

    const USER_PROFILE = 'user-profile';

    public static function getFullName($shortName)
    {
        static $nameMaps = array(
            self::APPLICATION => 'http://activitystrea.ms/schema/1.0/application',
            self::AUDIO => 'http://activitystrea.ms/schema/1.0/audio',

            self::COURSE => 'http://adlnet.gov/expapi/activities/course',
            self::INTERACTION => 'http://adlnet.gov/expapi/activities/interaction',
            self::QUESTION => 'http://adlnet.gov/expapi/activities/question',

            self::ONLINE_DISCUSSION => 'https://w3id.org/xapi/acrossx/activities/online-discussion',
            self::DOCUMENT => 'https://w3id.org/xapi/acrossx/activities/document',
            self::CLASS_ONLINE => 'https://w3id.org/xapi/acrossx/activities/class-online',
            self::VIDEO => 'https://w3id.org/xapi/acrossx/activities/video',
            self::MESSAGE => 'https://w3id.org/xapi/acrossx/activities/message',
            self::SEARCH_ENGINE => 'https://w3id.org/xapi/acrossx/activities/search-engine',

            self::LIVE => 'http://xapi.edusoho.com/activities/live',
            self::HOMEWORK => 'http://xapi.edusoho.com/activities/homework',
            self::EXERCISE => 'http://xapi.edusoho.com/activities/exercise',
            self::TEST_PAPER => 'http://xapi.edusoho.com/activities/testpaper',

            self::USER_PROFILE => 'http://id.tincanapi.com/activitytype/user-profile',
        );

        if (isset($nameMaps[$shortName])) {
            return $nameMaps[$shortName];
        } else {
            throw new \InvalidArgumentException(sprintf('UnSupport type %s', $shortName));
        }
    }
}
