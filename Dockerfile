FROM debian:8.6

#MAINTAINER Simon Wood <wuqian@howzhi.com>

ENV EDUSOHO_VERSION="7.2.9" \
    TIMEZONE="Asia/Shanghai" \
    PHP_MEMORY_LIMIT="1024M" \
    PHP_MAX_UPLOAD="1024M" \
    PHP_MAX_POST="1024M"

#init
# COPY docker/debian/jessie-sources.list /etc/apt/sources.list
RUN apt-get update && apt-get install -y tzdata && cp /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo "${TIMEZONE}" > /etc/timezone

#nginx
RUN  apt-get install -y nginx \
    && lineNum=`sed -n -e '/sendfile/=' /etc/nginx/nginx.conf`; sed -i $((lineNum+1))'i client_max_body_size 1024M;' /etc/nginx/nginx.conf \
    && sed -i '1i daemon off;' /etc/nginx/nginx.conf \

#php
    && apt-get install -y php5 php5-cli php5-curl php5-fpm php5-intl php5-mcrypt php5-mysqlnd php5-gd \
    && sed -i "s/;*post_max_size\s*=\s*\w*/post_max_size = ${PHP_MAX_POST}/g" /etc/php5/fpm/php.ini \
    && sed -i "s/;*memory_limit\s*=\s*\w*/memory_limit = ${PHP_MEMORY_LIMIT}/g" /etc/php5/fpm/php.ini \
    && sed -i "s/;*upload_max_filesize\s*=\s*\w*/upload_max_filesize = ${PHP_MAX_UPLOAD}/g" /etc/php5/fpm/php.ini \
#fpm
#   && sed -i "s/;*daemonize\s*=\s*yes/daemonize = no/g" /etc/php5/fpm/php-fpm.conf
    && sed -i "s/;*listen.owner\s*=\s*www-data/listen.owner = www-data/g" /etc/php5/fpm/pool.d/www.conf \
    && sed -i "s/;*listen.group\s*=\s*www-data/listen.group = www-data/g" /etc/php5/fpm/pool.d/www.conf \
    && sed -i "s/;*listen\s*=\s*\S*/listen = 127.0.0.1:9000/g" /etc/php5/fpm/pool.d/www.conf \

#mysql
    # && DEBIAN_FRONTEND=noninteractive apt-get install -y mysql-server \

#edusoho configuration
    # && apt-get install -y wget \
    && mkdir -p /var/www \
    # && wget -P /var/www http://download.edusoho.com/edusoho-${EDUSOHO_VERSION}.tar.gz \
    # && apt-get remove -y wget \

    && apt-get -y autoremove \
    && apt-get clean

COPY docker/nginx/edusoho.conf /etc/nginx/sites-enabled/
RUN rm -rf /etc/nginx/sites-enabled/default
           
COPY docker/entrypoint.sh /usr/bin/entrypoint.sh

COPY . /var/www/edusoho/
RUN chown -R www-data:www-data /var/www/edusoho/app/cache
RUN chown -R www-data:www-data /var/www/edusoho/app/logs
RUN chmod +x /usr/bin/entrypoint.sh



EXPOSE 80
CMD ["entrypoint.sh"]