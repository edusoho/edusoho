#! /bin/bash

SKIP="1"  
search_dir='plugins'
plugin='_'

if [ $#  -gt 2 ] ; then
    echo $(tput setaf 2)bin/build-app        $(tput sgr 0)--- build All app  in plugins folder
    echo $(tput setaf 2)bin/build-app n      $(tput sgr 0)--- build All app  in plugins folder and compile js
    echo $(tput setaf 2)bin/build-app Vip    $(tput sgr 0)--- build Vip app
    echo $(tput setaf 2)bin/build-app Vip  n $(tput sgr 0)--- build Vip app and compile js
    exit
fi

if [ $#  -eq 1 ] ; then
    if [ $1 = 'y' ] || [ $1 = 'n' ]; then 
        SKIP=$1
    else
        plugin=$1
    fi
fi

if [ $#  -eq 2 ] ; then
    plugin=$1
    SKIP=$2
fi


if [ "$SKIP" = 'n' ]; then
    # remove node modules folder and caches folder 
    echo $(tput setaf 2)remove old node_modules and app/caches using  command  $(tput bold)rm -rf app/caches node_modules $(tput sgr 0)
    rm -rf app/caches node_modules
    yarn

    #compile static resource file with webpack
    echo $(tput setaf 2)compile static resource file with webpack using command  $(tput bold)npm run compile$(tput sgr 0)
    npm run compile
else
    echo  $(tput setaf 1)WARING!!! skip compile js . $(tput sgr 0)
fi

if [ $plugin != '_' ]; then 
    if [ ! -d $(pwd)/$search_dir/$plugin"Plugin" ]; then
        echo 插件不存在，打包失败
    else
        cd $search_dir/$plugin"Plugin"
        git pull
        cd ../..
        app/console build:plugin-app  $plugin
    fi
else
    for entry in `ls $search_dir`; do
        echo $(tput setaf 2) -- pull $entry using command  $(tput bold)cd $search_dir/$entry   git pull  $(tput sgr 0)

        cd $search_dir/$entry
        git pull
        cd ../..
        plugin=$(echo $entry | awk -F"Plugin" '{print $1}')  
        app/console   build:plugin-app  $plugin
        
    done
fi

