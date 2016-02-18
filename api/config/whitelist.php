<?php

return array(
    'GET'  => array(
        '/^\/api\/users$/',
        '/^\/api\/users\/pages$/',
        '/^\/api\/users\/\d+$/',
        '/^\/api\/mobileschools\/.+$/'
    ),
    'POST' => array(
        '/^\/api\/users$/',
        '/^\/api\/users\/login$/',
        '/^\/api\/users\/bind_login$/'
    )
);
