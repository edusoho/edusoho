#!/bin/bash

if [ "$#" -eq "3" ];then
    wget http://ojc8jepus.bkt.clouddn.com/functional-latest.sql;
    if [ $3 ]; then
        mysql -u"$2" -p"$3" -e"drop database IF EXISTS \`$1\`;"
        mysql -u"$2" -p"$3" -e"CREATE DATABASE \`$1\` DEFAULT CHARACTER SET utf8;"
        mysql -u"$2" -p"$3" -e"use \`$1\`; source functional-latest.sql;"
    else
        mysql -u"$2"  -e"drop database IF EXISTS \`$1\`;"
        mysql -u"$2"  -e"CREATE DATABASE \`$1\` DEFAULT CHARACTER SET utf8;"
        mysql -u"$2"  -e"use \`$1\`; source functional-latest.sql;"
    fi
else
echo -e "please print your dbname ,dbuser, passwd!"
fi
exit 0