FROM debian:8.6

#MAINTAINER Simon Wood <wuqian@howzhi.com>

ENV EDUSOHO_VERSION="7.2.9" \
    TIMEZONE="Asia/Shanghai" \
    PHP_MEMORY_LIMIT="1024M" \
    PHP_MAX_UPLOAD="1024M" \
    PHP_MAX_POST="1024M"

#init
# COPY debian/jessie-sources.list /etc/apt/sources.list
RUN apt-get update