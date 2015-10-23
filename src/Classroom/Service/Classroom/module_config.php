<?php

return array(
    'event_subscriber' => array(
        'Classroom\\Service\\Classroom\\Event\\ClassroomEventSubscriber',
    ),
    'thread.event_processor' => array(
        'classroom' => 'Classroom\\Service\\Classroom\\Event\\ClassroomThreadEventProcessor',
    ),
);
