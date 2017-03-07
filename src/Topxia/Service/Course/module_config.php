<?php

return array(
    'event_subscriber' => array(
        'Topxia\\Service\\Course\\Event\\CourseEventSubscriber',
        'Topxia\\Service\\Course\\Event\\CourseLessonEventSubscriber',
        'Topxia\\Service\\Course\\Event\\CourseMaterialEventSubscriber',
        'Topxia\\Service\\Course\\Event\\CourseMemberEventSubscriber',
        'Topxia\\Service\\Course\\Event\\ClassroomCourseExpiryDateEventSubscriber'
    )
);
