#!/usr/bin/env sh
#git clean -fdx
mysql -uroot -e 'DROP DATABASE `edusoho`'
mysql -uroot -e 'CREATE DATABASE `edusoho` DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci'

cp -n app/config/parameters.yml.dist app/config/parameters.yml
rm -rf app/cache app/logs app/data web/files node_modules
mkdir -p app/cache app/logs app/data web/files
chmod 777 app/cache app/logs app/data web/files

php bin/phpmig migrate
php app/console system:init
php app/console assets:install web --symlink --relative

touch .webpack-watch.log
yarn