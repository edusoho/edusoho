<?php

return array(
    'GET'  => array(
        '/^\/api\/users$/',
        '/^\/api\/users\/pages$/',
        '/^\/api\/users\/\d+$/',
        '/^\/api\/mobileschools\/.+$/',
        '/^\/api\/classrooms\/\w+\/members$/',
        '/^\/api\/discovery_columns$/',
        '/^\/api\/courses\/discovery\/columns$/',
        '/^\/api\/classrooms\/discovery\/columns$/',
        '/^\/api\/lessons$/',
        '/^\/api\/lessons\/\d+$/',
        '/^\/api\/classroom_play\/\d+$/',
        '/^\/api\/course\/\d+\/lessons$/'
    ),
    'POST' => array(
        '/^\/api\/users$/',
        '/^\/api\/users\/login$/',
        '/^\/api\/users\/bind_login$/'
    )
);
