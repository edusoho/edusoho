#!/bin/bash

service nginx start;
ps aux|grep nginx;
wget http://ojc8jepus.bkt.clouddn.com/es-latest.conf;
