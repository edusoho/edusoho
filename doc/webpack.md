## webpack前端方案初始化（需安装nodejs环境）

### 前言

整个webpack方案已经上传到[npm](https://www.npmjs.com/)里,
随着项目的需要，会不断迭代
```
npm install es-webpack-engine --save-dev
```

### webpack配置文件说明

```
目录：/app/Resources/webpack/

> 本项目中的打包资源配置文件:
settings.js 

> 开发模式优化配置文件
webpack.config.js

> 开发环境下的入口文件 --- 不推荐修改
webpack.dev.js

> 生产环境下的入口文件 --- 不推荐修改
webpack.js
```

### 依赖安装

```
npm install
```
```
# 为提高下载速度，可添加淘宝镜像
npm install -g cnpm --registry=https://registry.npm.taobao.org
cnpm install
```

### nginx添加配置项
```
location ~ ^/static-dist {
  if (-f $document_root/static-dist/dev.lock)
  {
    rewrite ^(.*)$ http://127.0.0.1:3030$1 last;
  }
}

其中3030可修改，static-dist为settings.js文件中config.output.publicPath的值
```

### 开发模式

```
npm start
npm start port:3038 #改变端口
```

```
# 此命令默认会绑定到3030端口，但不会生成真实文件，但可以通过http://127.0.0.1:3030/static-dist 浏览到文件目录，
其中static-dist为settings.js文件中config.output.publicPath的值

```

### 最终编译

```
# 会生成实体文件，本项目会生成到web/static-dist/

npm run compile
npm run compile:debug  #不压缩
```

### package.json 版本约定

* devDependencies

```
放入开发工具的依赖，即不会出现在编译后的文件中，版本默认用 ^ 开头

使用下面命令新增
npm install xxx --save-dev 
```

* dependencies

```
放入功能开发需要的依赖，即会出现在编译后的文件中,

版本使用分为两部分，一部分是第三方的依赖，限定具体版本，另一部分即我们自己上传的npm依赖，则使用 'latest'

使用下面命令新增
npm install xxx@x.x.x --save 
或
npm install xxx@latest --save
```

### 前端资源目录说明

```
# 主程序开发资源根目录：/app/Resources/static-src/ (以下简称globalDir)

# 前后台业务目录
globalDir/
  app/
    js/
      admin/
        default/
          index.js
        main.js
      default/
        index.js
      user/
        index.js
      main.js
    less/
      admin/
        main.less
      main.less

# libs目录 --- 在twig {% do script %} 里引入
globalDir/
  libs/
    js/
      xxx.js
    less/
      xxx.less
    vendor.js
    vendor.less

# common目录 --- 公用js组件，在页面js中import引入
globalDir/
  common/
    xxx.js

# 插件目录,以CrmPlugin为例
# 插件资源根目录：/plugins/CrmPlugin/Resources/static-src (以下简称pluginDir)
pluginDir/
  js/
    common/
      xxx.js
    default/
      index.js
    main.js
  less/
    main.less

# 其它说明
- 每个具有main.js的目录，编译时都会在同目录下生成common.js
- 以app的layout.html.twig为例：
  {% do css(['libs/vendor.css', 'app/css/main.css']) %}
  {% do script(['libs/vendor.js', 'app/js/common.js', 'app/js/main.js']) %}
  更多代码示例可参考：https://github.com/ketuzhong/biz-symfony-starter
```

### 已实现功能
总：可处理所有前端资源
* 文件纯拷贝功能
* 支持主题可配色
* webpack编译报错通知
* 分app、libs、plugins输出
* 字体图标、图像、swf等纳入编译流


### 待改进
* dev模式下scanPath的新文件无法监听的问题
