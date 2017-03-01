#!/bin/bash

wget http://ojc8jepus.bkt.clouddn.com/functional-nginx-conf;
mv functional-nginx-conf /etc/nginx/sites-enabled/;
service nginx start;
ps aux|grep nginx;
echo 'nginx started...';
service php5-fpm start;
echo 'php5-fpm started...';
