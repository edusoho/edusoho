# edusoho-h5 (h5微网校项目)

> 相关文档

- [接口地址](http://kb.codeages.net/edusoho/api/api-h5.html)
- [文档播放器地址](http://coding.codeages.net/qiqiuyun/api-doc/blob/master/v2/resource-play.md)
- [视频播放器文档](/doc/player.md)
- [需求文档](https://pro.modao.cc/app/43be7ceee9ba1239e1366453d273907de9ac2043#screen=sFAABE922B31526366021396)

## Build Setup

``` bash
# 安装依赖 (需要锁定版本安装依赖，不然 vant新版本引入方式有更改，会导致报错)
yarn

# 开发阶段
npm run dev:h5
npm run dev:admin

# build 打包
npm run build
npm run build:h5
npm run build:admin

# analyze 分析项目依赖
npm run analyze:h5
npm run analyze:admin

```

## 发布到测试站

1、安装 composer 中的依赖

```
composer require deployer/deployer --dev
```


2、找后端人员给予 deployerkey 文件（允许 ssh 到服务器的验证文件）
  放到~/.ssh/deployerkey目录下
  设置权限 600

```
sudo chmod 600 ~/.ssh/deployerkey
```

3、打包发布代码到 try 服务器（测试站地址: http://lvliujie.st.edusoho.cn, http://zhangfeng.st.edusoho.cn）

```
php vendor/bin/dep deploy dev
```



