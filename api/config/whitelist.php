<?php

return array(
    'GET'  => array(
        '/^\/api\/users\/\d+$/',
        '/^\/api\/mobileschools\/.+$/',
        '/^\/api\/classrooms\/\w+\/members$/',
        '/^\/api\/discovery_columns$/',
        '/^\/api\/courses\/discovery\/columns$/',
        '/^\/api\/classrooms\/discovery\/columns$/',
        '/^\/api\/lessons$/',
        '/^\/api\/lessons\/\d+$/',
        '/^\/api\/classroom_play\/\d+$/',
        '/^\/api\/course\/\d+\/lessons$/',
        '/^\/api\/setting\/\w+$/',
        '/^\/api\/courses\/\w+\/members$/'
    ),
    'POST' => array(
        '/^\/api\/users$/',
        '/^\/api\/users\/login$/',
        '/^\/api\/users\/bind_login$/',
        '/^\/api\/sms_codes$/',
        '/^\/api\/users\/password$/',
        '/^\/api\/emails$/'
    )
);
