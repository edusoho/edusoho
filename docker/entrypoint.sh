#!/bin/bash

#set -eo pipefail

#check required env vars
# if [ -z "$DOMAIN" ] || [ -z "$MYSQL_USER" ] || [ -z "$MYSQL_PASSWORD" ]; then
#     echo >&2 'required option: -e DOMAIN="your_domain" -e MYSQL_USER="your_mysql_user" -e MYSQL_PASSWORD="your_mysql_password"'
#     exit 1
# fi

# hasInitd=
# if [ -f "/var/www/entrypoint-initd.lock" ]; then
#     hasInitd=true
# else
#     hasInitd=false
# fi

# if [ !hasInitd ]; then
    #extract edusoho
    # tar zxvf /var/www/edusoho-${EDUSOHO_VERSION}.tar.gz -C /var/www && chown -R www-data:www-data /var/www/edusoho && rm -rf /var/www/edusoho-${EDUSOHO_VERSION}.tar.gz
    # touch /var/www/entrypoint-initd.lock


    #mofidy domain for nginx vhost
    # sed -i "s/{{DOMAIN}}/${DOMAIN}/g" /etc/nginx/sites-enabled/edusoho.conf

    #init datadir if mount dir outside to /var/lib/mysql
    # sed -i "s/user\s*=\s*debian-sys-maint/user = root/g" /etc/mysql/debian.cnf
    # sed -i "s/password\s*=\s*\w*/password = /g" /etc/mysql/debian.cnf
    # mysql_install_db
# fi

#start services
# echo "starting mysql"
# /etc/init.d/mysql start
# mysql_root='mysql -uroot'
# echo 'SELECT 1' | ${mysql_root} &> /dev/null
# if [ "$?" -ne 0 ]; then
#     echo >&2 'mysql start failed.'
#     exit 1
# fi
# for i in {30..0}; do
#     if echo 'SELECT 1' | ${mysql_root} >& /dev/null; then
#         break
#     fi
#     echo "waiting..."
#     sleep 1
# done

# if [ "$i" = 0 ]; then
#     echo >&2 'mysql start failed.'
#     exit 1
# else
#     if [ !hasInitd ]; then
#         #create empty database
#         echo 'creating edusoho database'
#         ${mysql_root} <<-EOSQL
#             CREATE DATABASE IF NOT EXISTS edusoho DEFAULT CHARACTER SET utf8 ;
#             GRANT ALL PRIVILEGES ON edusoho.* TO "${MYSQL_USER}"@"localhost" IDENTIFIED BY "${MYSQL_PASSWORD}";
#             GRANT ALL PRIVILEGES ON edusoho.* TO "${MYSQL_USER}"@"127.0.0.1" IDENTIFIED BY "${MYSQL_PASSWORD}";

# EOSQL
#     fi
# fi
chown -R 33:999 "/var/www/edusoho/app/data"
chown -R 33:999 "/var/www/edusoho/web/files"
echo 'starting php5-fpm'
/etc/init.d/php5-fpm start >& /dev/null

echo 'starting nginx'
echo '***************************'
echo '* welcome to use edusoho! *'
echo '***************************'
nginx
