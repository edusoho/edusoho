# EduSoho开发环境配置

建议在Linux下做EduSoho的开发，以下的配置基于Linux的发行版Ubuntu。

## 安装开发的初始环境

EduSoho开发需要安装Git, Nginx, PHP, Mysql，这些软件包的安装我就不再叙述了。

## 安装php56 
    sudo add-apt-repository ppa:ondrej/php
    sudo apt update 
    sudo apt install php5.6 php5.6-cli php5.6-curl php5.6-fpm php5.6-intl php5.6-mcrypt php5.6-mysqlnd php5.6-gd php5.6-redis
## 下载EduSoho源码
    * git clone https://github.com/EduSoho/EduSoho.git /var/www/edusoho

由于众所周知的原因，国内访问github的网络慢，这一步应该需要些时间，请耐心等待。有权限访问公司内网，优先使用公司内网地址。

## 进入程序目录
    cd /var/www/edusoho

## 创建配置文件
    cp app/config/parameters.yml.dist app/config/parameters.yml

并修改配置文件中的数据库配置

## 创建数据库

  * 进入MySQL命令行：
    ````
    mysql -uroot -p
    ````

  * 在mysql命令行下，创建数据库：
    ````
    mysql> CREATE DATABASE `edusoho` DEFAULT CHARACTER SET utf8 ; 
    mysql> exit;
    ````

## 初始化程序基础数据
    bin/phpmig migrate
    app/console system:init

## 配置Nginx

以下配置，需要根据实际情况修改下php-fpm的配置。

    server {
        listen 80;

        server_name www.edusoho-dev.com;

        root /var/www/edusoho/web;

        access_log /var/log/nginx/edusoho.access.log;
        error_log /var/log/nginx/edusoho.error.log;

        location / {
            index app.php;
            try_files $uri @rewriteapp;
        }

        location @rewriteapp {
            rewrite ^(.*)$ /app.php/$1 last;
        }

        location ~ ^/static-dist {
            if (-f $document_root/static-dist/dev.lock)
            {
                rewrite ^(.*)$ http://127.0.0.1:3030$1 last;
            }
        }

        location ~ ^/udisk {
            internal;
            root /var/www/edusoho/app/data/;
        }

        location ~ ^/(app|app_dev)\.php(/|$) {
            # [改] 请根据实际php-fpm运行的方式修改
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

## 浏览器打开 www.edusoho-dev.com
    默认账号为：test@edusoho.com
    密码为：kaifazhe
