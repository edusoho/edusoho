# EduSoho开发环境配置(MAC版)

以下的配置基于MAC

## 安装开发的初始环境

一、安装homebrew 

    curl -LsSf http://github.com/mxcl/homebrew/tarball/master | sudo tar xvz -C/usr/local --strip 1

二、安装nginx 

    sudo brew install nginx

三、安装mysql

    sudo brew install mysql 

四、添加源 

    sudo brew tap homebrew/dupes 
    sudo brew tap josegonzalez/homebrew-php 

五、安装php 

    5.1 php56

    brew install php55 --with-fpm --with-gmp --with-homebrew-openssl --with-imap --with-intl --with-libmysql --without-bz2 --without-mysql --without-pcntl --without-pear --with-cli --with-curl --with-mcrypt --with-mysqlnd_ms --with-gd

    5.2 php56

    brew install php56 --with-fpm --with-gmp --with-homebrew-openssl --with-imap --with-intl --with-libmysql --without-bz2 --without-mysql --without-pcntl --without-pear --with-cli --with-curl --with-mcrypt --with-mysqlnd_ms --with-gd --without-apache 

## 下载EduSoho源码

    * git clone https://gitcafe.com/Topxia/EduSoho /var/www/edusoho-dev
    注：/var/www/edusoho-dev 为本地要放的目录,如果要修改，下面出现的目录相应变化

    由于众所周知的原因，国内访问github的网络慢，这一步应该需要些时间，请耐心等待。

## 进入程序目录

    cd /var/www/edusoho-dev

## 安装Composer

    curl -sS https://getcomposer.org/installer | php

## 创建数据库

  * 进入MySQL命令行：

        mysql -uroot -p

  * 在mysql命令行下，创建数据库：

        mysql> CREATE DATABASE `edusoho-dev` DEFAULT CHARACTER SET utf8 ; 
        mysql> exit;


## 初始化程序基础数据

    bin/phpmig migrate
    app/console system:init


## 配置Nginx

    nginx -V 

查看nginx的配置文件路径

    --conf-path=/usr/local/etc/nginx/nginx.conf

获取到的应该是上面类似的路径

    sudo vi /usr/local/etc/nginx/nginx.conf

进入配置文件目录，开始编辑,在nginx.conf中添加如下代码

    server {
        listen 80;

        server_name esdev.com www.esdev.com;

        root /Users/ketu/Sites/edusoho/web;

        access_log /Users/ketu/Sites/logs/source.access.log;
        error_log /Users/ketu/Sites/logs/source.error.log;

        location / {
            index app_dev.php;
            try_files $uri @rewriteapp;
        }

        location @rewriteapp {
            rewrite ^(.*)$ /app_dev.php/$1 last;
        }

        location ~ ^/static-dist {
            if (-f $document_root/static-dist/dev.lock)
            {
                rewrite ^(.*)$ http://127.0.0.1:3030$1 last;
            }
        }

        location ~ ^/udisk {
            internal;
            root  /Users/ketu/Sites/edusoho/app/data/;
        }

        location ~ ^/(app|app_dev)\.php(/|$) {
            fastcgi_pass   unix:/tmp/php-fpm.sock;
            fastcgi_split_path_info ^(.+\.php)(/.*)$;
            include fastcgi_params;
            fastcgi_param  SCRIPT_FILENAME    $document_root$fastcgi_script_name;
            fastcgi_param  HTTPS              off;
            fastcgi_param HTTP_X-Sendfile-Type X-Accel-Redirect;
            fastcgi_param HTTP_X-Accel-Mapping /udisk=/Users/ketu/Sites/edusoho/app/data/udisk;
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
            fastcgi_pass   unix:/tmp/php-fpm.sock;
            fastcgi_split_path_info ^(.+\.php)(/.*)$;
            include fastcgi_params;
            fastcgi_param  SCRIPT_FILENAME    $document_root$fastcgi_script_name;
            fastcgi_param  HTTPS              off;
        }
    }

将上面出现的/Users/ketu/Sites/edusoho/ 替换成你自己的edusoho目录

过程中用到的命令： i进入编辑模式  按esc键 ＋ :wq 保存退出 :q 直接退出 :q! 强制退出 

##  /etc/hosts 里添加 

    127.0.0.1   esdev.com
    127.0.0.1   www.esdev.com


## 七、启动服务

    sudo nginx 
    mysql.server start
    sudo nohup php-fpm55 &

