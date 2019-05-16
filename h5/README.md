# edusoho-h5 (h5微网校项目)

> 相关文档

- [接口地址](http://kb.codeages.net/edusoho/api/api-h5.html)
- [文档播放器地址](http://coding.codeages.net/qiqiuyun/api-doc/blob/master/v2/resource-play.md)
- [视频播放器文档](/doc/player.md)
- [需求文档](https://pro.modao.cc/app/43be7ceee9ba1239e1366453d273907de9ac2043#screen=sFAABE922B31526366021396)


## 分支说明

1、beta/x.x.x 是当前迭代开发分支

2、release/x.x.x 是当前迭代待发布分支，每次发布成功后需要 新建一个版本的tag作为标记。例如：

```
git tag -a v1.0.12 -m 'v1.0.12' // 新建tag

git push origin v1.0.12         // push 到远程
```

然后提交 mergeRequest 合并到 master、develop 分支（后续可以自动化）

3、master 稳定分支

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

开发中所有接口使用的服务器域名配置在 config/index.js proxyTable 配置里。
**后台开发时**需要当前用户具有管理员权限，需要在 admin/api/interceptors.js 里配置 h5 里登录的管理员权限用户的token （h5 登录时 网络请求的 /token 接口里有）

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

##  目录说明

```
...
- admin           // 后台配置开发目录
  + api           // 接口配置
  + config        // 业务配置文件
  + containers    // 后台页面（里面一个文件夹代表一个页面，页面入口为 index.vue）
  + mixins        // mixins 复用模块
  + router        // 路由
  + store         // vuex 文件
  + styles        // 样式(里面页面级样式在 container 文件夹内，组件级样式在 modules 文件夹里)
  + utils         // 工具类
  + App-admin.vue // 项目单页入口
  + main-admin.js。// 打包入口
- src              // h5 微网校开发目录
  + api            // 接口配置
  + assets         // 存放字体和样式
  + components     // 全局组件
  + config         // 业务配置文件
  + containers     // 页面（一个文件夹代表一个页面，页面入口为 index.vue）
  + filters        // 全局过滤器
  + mixins         // mixins 复用模块
  + router         // 路由
  + store          // vuex
  + utils          // 工具类
  + App.vue        // 项目单页入口
  + admn.js        // 打包入口
- static          // 静态资源，图片等
...
```


