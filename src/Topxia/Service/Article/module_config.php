<?php

return array(
    'event_subscriber' => array(
        'Topxia\\Service\\Article\\Event\\ArticleEventSubscriber',
    ),
    'thread.event_processor' => array(
        'article' => 'Topxia\\Service\\Article\\Event\\ArticleEventSubscriber',
    ),
);
