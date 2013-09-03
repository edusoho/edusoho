* 执行： app/console doctrine:migrations:migrate

* 执行： app/console topxia:init

* 配置nginx，示例配置文件如下：

server {
    listen 80;
    server_name esdev.com www.esdev.com edusoho.keephub.com;
    root /var/www/edusoho/web;

    access_log /var/log/nginx/esdev.com.access.log;
    error_log /var/log/nginx/esdev.com.error.log;

    location / {
        index app_dev.php;
        if ($host != 'www.esdev.com' ) {
            rewrite ^/(.*)$ http://www.esdev.com/$1 permanent;
        }
        try_files $uri @rewriteapp;
    }

    location ^~ /cache/ {
        try_files $uri @rewriteapp;
    }

    location @rewriteapp {
        rewrite ^(.*)$ /app_dev.php/$1 last;
    }

    location ~ ^/udisk {
        internal;
        root /var/www/edusoho/app/data/;
    }

    location ~ ^/(app|app_dev)\.php(/|$) {
        fastcgi_pass   unix:/var/run/php5-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param  SCRIPT_FILENAME    $document_root$fastcgi_script_name;
        fastcgi_param  HTTPS              off;
        fastcgi_param HTTP_X-Sendfile-Type X-Accel-Redirect;
        fastcgi_param HTTP_X-Accel-Mapping /udisk=/var/www/edusoho/app/data/udisk;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 8 128k;

    }

    location ~* \.(jpg|jpeg|gif|png|ico|swf)$  {
        expires 3y;
        access_log off;
        gzip off;
    }

    location ~* \.(css|js)$ {
        access_log off;
        expires 3y;
    }

    location ~ ^/files/.*\.(php|php5)$ {
        deny all;
    }

    location ~ \.php$ {
        fastcgi_pass   unix:/var/run/php5-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param  SCRIPT_FILENAME    $document_root$fastcgi_script_name;
        fastcgi_param  HTTPS              off;
    }
}

* 使用帐号test@edusoho.com，密码：testtest登录。