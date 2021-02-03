#!/usr/bin/env sh
git clean -fdx
cp -n app/config/parameters.yml.dist app/config/parameters.yml
mkdir -p app/cache app/logs app/data web/files
chmod 777 app/cache app/logs app/data web/files

php bin/phpmig migrate
php app/console system:init
php app/console assets:install web --symlink --relative

touch .webpack-watch.log
yarn