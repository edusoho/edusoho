# 配置资料库插件

以下步骤适用于本地开发。

## 1. 克隆代码

    cd /var/www/edusoho-dev
    git clone ssh://git@gitlab.howzhi.net:4422/edusohoplugin/materiallib.git plugins/MaterialLib

## 2. 安装插件

    app/console plugin:register MaterialLib
