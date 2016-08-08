<?php

return array(
    'event_subscriber'       => array(
        'Topxia\\Service\\OpenCourse\\Event\\OpenCourseEventSubscriber'
    ),
    'thread.event_processor' => array(
        'openCourse' => 'Topxia\\Service\\OpenCourse\\Event\\OpenCourseThreadEventProcessor'
    )
);
