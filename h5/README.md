# edusoho-h5 (h5微网校项目)

> 相关文档

- [接口地址](http://kb.codeages.net/edusoho/api/api-h5.html)
- [播放器地址](http://docs.qiqiuyun.com/)
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

开发中所有接口使用的服务器域名配置在 config/index.js proxyTable 配置里。
**后台开发时**需要当前用户具有管理员权限，需要在 admin/api/interceptors.js 里配置 h5 里登录的管理员权限用户的token （h5 登录时 网络请求的 /token 接口里有）

##  目录说明

```
...
- admin           // 后台配置开发目录
  + api           // 接口配置
  + config        // 业务配置文件
  + containers    // 后台页面（里面一个文件夹代表一个页面，页面入口为 index.vue）
  + mixins        // mixins 复用模块
  + router        // 路由
  + store         // vuex 文件8.8.3
  + styles        // 样式(里面页面级样式在 container 文件夹内，组件级样式在 modules 文件夹里)
  + utils         // 工具类
  + App-admin.vue // 项目单页入口8.8.3
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


