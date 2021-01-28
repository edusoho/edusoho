<?php

return array(
    'event_subscriber' => array(
        'Topxia\\Service\\User\\Event\\UserEventSubscriber',
        'Topxia\\Service\\User\\Event\\MessageEventSubscriber',
        'Topxia\\Service\\User\\Event\\OrderEventSubscriber',
        'Topxia\\Service\\User\\Event\\StatusEventSubscriber',
    ),
);
