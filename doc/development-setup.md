# EduSoho开发环境配置

建议在Linux下做EduSoho的开发，以下的配置基于Linux的发行版Ubuntu。

## 安装开发的初始环境

EduSoho开发需要安装Git, Nginx, PHP, Mysql，这些软件包的安装我就不再叙述了。

## 下载EduSoho源码

    git clone https://code.csdn.net/TopxiaCOM/edusoho.git /var/www/edusoho-dev

由于众所周知的原因，国内访问github的网络慢，这一步应该需要些时间，请耐心等待。

## 进入程序目录

    cd /var/www/edusoho-dev

## 安装Composer

    curl -sS https://getcomposer.org/installer | php

## 安装程序的依赖库(Vendor)

    php composer.phar install

由于众所周知的原因，这一步也会花点时间，如果中途由于网络超时，只要重新执行上面的命令就可以了，会继续install的。
在最后一步会问你数据库的设置，如下：

    Some parameters are missing. Please provide them.
    database_driver (pdo_mysql):    
    database_host (127.0.0.1): 
    database_port (null): 
    database_name (symfony): edusoho-dev
    database_user (root): 
    database_password (null): 
    mailer_transport (smtp): 
    mailer_host (127.0.0.1): 
    mailer_user (null):  
    mailer_password (null): 
    locale (en): zh_CN
    secret (ThisTokenIsNotSoSecretChangeIt): 

## 创建数据库

  * 进入MySQL命令行：

        mysql -uroot -p

  * 在mysql命令行下，创建数据库：

        mysql> CREATE DATABASE `edusoho-dev` DEFAULT CHARACTER SET utf8 ; 
        mysql> exit;


## 初始化程序基础数据

    app/console doctrine:migrations:migrate
    app/console topixa:init

## 配置Nginx

以下配置，需要根据实际情况修改下php-fpm的配置。

    server {
        listen 80;

        server_name www.edusoho-dev.com;

        root /var/www/edusoho-dev/web;

        access_log /var/log/nginx/edusoho-dev.com.access.log;
        error_log /var/log/nginx/edusoho-dev.com.error.log;

        location / {
            index app.php;
            try_files $uri @rewriteapp;
        }

        location @rewriteapp {
            rewrite ^(.*)$ /app.php/$1 last;
        }

        location ~ ^/udisk {
            internal;
            root /var/www/edusoho-dev/app/data/;
        }

        location ~ ^/(app|app_dev)\.php(/|$) {
            # [改] 请根据实际php-fpm运行的方式修改
            fastcgi_pass   unix:/var/run/php5-fpm.sock;
            fastcgi_split_path_info ^(.+\.php)(/.*)$;
            include fastcgi_params;
            fastcgi_param  SCRIPT_FILENAME    $document_root$fastcgi_script_name;
            fastcgi_param  HTTPS              off;
            fastcgi_param HTTP_X-Sendfile-Type X-Accel-Redirect;
            fastcgi_param HTTP_X-Accel-Mapping /udisk=/var/www/edusoho-dev/app/data/udisk;
            fastcgi_buffer_size 128k;
            fastcgi_buffers 8 128k;
        }

        location ~* \.(jpg|jpeg|gif|png|ico|swf)$ {
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
            # [改] 请根据实际php-fpm运行的方式修改
            fastcgi_pass   unix:/var/run/php5-fpm.sock;
            fastcgi_split_path_info ^(.+\.php)(/.*)$;
            include fastcgi_params;
            fastcgi_param  SCRIPT_FILENAME    $document_root$fastcgi_script_name;
            fastcgi_param  HTTPS              off;
        }
    }

## /etc/hosts里添加

    127.0.0.1 www.edusoho-dev.com

## 浏览器打开www.eduosho-dev.com

    默认账号为：test@edusoho.com
    密码为：kaifazhe