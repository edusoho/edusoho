## webpack前端方案初始化（需安装nodejs环境，推荐node版本为5.12.0）

### 前言

整个webpack方案已经上传到[npm](https://www.npmjs.com/)里,
随着项目的需要，会不断迭代
```
npm install es-webpack-engine --save-dev
```

### webpack配置文件说明(webpack.config.js)

```js
const options = {
    output: {
        path: 'web/static-dist/',       // 用于生产环境下的输出目录
        publicPath: '/static-dist/',    // 用于开发环境下的输出目录
    },
    libs: { // 共用的依赖
        "vendor": ['libs/vendor.js'], //可以是一个js文件,
        "html5shiv": ['html5shiv'],
        "fix-ie": ['console-polyfill', 'respond-js'], //也可以是一个npm依赖包
        "jquery-insertAtCaret": ['libs/js/jquery-insertAtCaret.js'],
    },
    noParseDeps: [ //不需要解析的依赖，加快编译速度
        'jquery/dist/jquery.js',
        'bootstrap/dist/js/bootstrap.js',
    },
    onlyCopys: [ //纯拷贝文件到输出的libs目录下
    {
      name: 'es-ckeditor',
      ignore: [
        '**/samples/**',
        '**/lang/!(zh-cn.js)',
        '**/kityformula/libs/**',
      ]
    }]
};

export default options;
```

### 依赖安装

为避免npm因版本问题出现一些未知错误，统一使用cnpm来安装依赖

```
# 添加淘宝镜像(安装4.4.0以上版本)
npm install -g cnpm --registry=https://registry.npm.taobao.org
```
```
cnpm install
```

### nginx添加配置项
为了开发环境下，可以访问到webpack打包的资源
```
set $webpack_server http://127.0.0.1:3030;

location @webpack {
    proxy_pass $webpack_server;
}

location ~ ^/static-dist {
    try_files $uri @webpack;
}

其中3030可修改，static-dist为webpack.config.js文件中config.output.publicPath的值
```

### 开发模式

```
npm run dev
npm start 
npm start port:3000 //  修改端口
npm start openModule:lib,app,admin,plugin,copy,theme,custom // 修改开启编译的模块
npm start devtool:source-map // 修改编译模式
```

```
# 此命令默认会绑定到3030端口，但不会生成真实文件，但可以通过http://127.0.0.1:3030/static-dist 浏览到文件目录，
其中static-dist为swebpack.config.js文件中config.output.publicPath的值
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
放入开发工具的依赖，即不会出现在编译后的文件中，限定具体版本安装

使用下面命令新增
npm install xxx@x.x.x --save-dev 
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

# 其它说明--必读
- 每个具有main.js的目录，编译时都会在同目录下生成common.js

- 以app的layout.html.twig为例：
  {% do css(['libs/vendor.css', 'app/css/main.css']) %}
  {% do script(['libs/vendor.js', 'app/js/common.js', 'app/js/main.js']) %}
  更多代码示例可参考：https://github.com/ketuzhong/biz-symfony-starter

- 约定index.js 为每个页面的打包入口文件，其它文件名仅作为片段、模块被其它js文件引入（import）

— vendor.js vendor.less 为前后台页面都需要引入的资源，如果只是前台页面用到则放到main.js

- 理解清楚libs与common目录下的资源差异
```

### 最佳实践

1. 在js引入资源的时候，建议用全局root目录(app/Resources/static-src)下的目录取代较长的相对路径
如app/Resources/static-src/app/js/testpaper-manage/questions/index.js

```
import BatchSelect from '../../../common/widget/batch-select';
import QuestionOperate from '../../../common/component/question-operate';
```

修改为：

```
import BatchSelect from 'app/common/widget/batch-select';
import QuestionOperate from 'app/common/component/question-operate';
```

亦可使用alias别名来简化路径：

```
{
  nodeModulesDir: '/node_modules',
  xxxplugin: '/plugins/xxxPlugin/Resources/static-src'
}

使用方式：
@import '~nodeModulesDir/xx/xxx.less'; //引入less、css时前面需加'~'来让loader识别alias别名
import xxx from 'xxxplugin/xx/xxx.js';
```

### 重要更新记录
* 引入nodemon (2017-01-19) <br>
开发环境下，利用nodemon来重启node服务（当根目录下nodemon.json文件中的watch值里的目录或文件发生变化时）<br>
例如watch值中有<code>app/Resources/webpack</code> 则，该目录下的文件改动，会使node服务重启

* 对新增入口文件的支持 (2017-01-19) <br>
开发环境下，当node服务启动后，新增入口文件，会自动重启node服务。<br>
不过因为是重启，编译的时间较文件改动的时间长一些。

### 已实现功能
总：可处理所有前端资源

* 文件纯拷贝功能
* 支持主题可配色
* webpack编译报错通知
* 分app、libs、plugins输出
* 字体图标、图像、swf等纳入编译流

### 特别说明
- 模块组件样式（不希望单独打包出css文件的）以下面形式引入

```
import '!style!css!less!xxx.less';
```

### 常见问题

1.模块不存在
```
ERROR in multi libs/jquery-blurr
    Module not found: Error: Cannot resolve 'file' or 'directory' /Users/ketu/Sites/edudemo/node_modules/jquery-blurr/dist/jquery.blurr.js in /Users/ketu/Sites/edudemo
     @ multi libs/jquery-blurr
```
解决方法有：1)查看node_modules是否存在这个文件，如果没有，则运行<code>cnpm install</code>
2) 删除node_modules整个文件夹，运行<code>cnpm install</code>

2.内存泄漏
```
<--- Last few GCs --->

      14 ms: Mark-sweep 2.2 (37.1) -> 2.1 (38.1) MB, 2.8 / 0 ms [allocation failure] [GC in old space requested].
      15 ms: Mark-sweep 2.1 (38.1) -> 2.1 (39.1) MB, 1.2 / 0 ms [allocation failure] [GC in old space requested].
      16 ms: Mark-sweep 2.1 (39.1) -> 2.1 (39.1) MB, 0.9 / 0 ms [last resort gc].
      17 ms: Mark-sweep 2.1 (39.1) -> 2.1 (39.1) MB, 1.0 / 0 ms [last resort gc].


<--- JS stacktrace --->

==== JS stack trace =========================================

Security context: 0xf2e91fe3ac1 <JS Object>
    2: DefineOwnProperty(aka DefineOwnProperty) [native v8natives.js:641] [pc=0xbe5f9941dfb] (this=0xf2e91f04189 <undefined>,K=0x9a0d6f072a1 <JS Function EventEmitter (SharedFunctionInfo 0xf2e91ff6e41)>,W=0xf2e91ff6459 <String[19]: defaultMaxListeners>,H=0x9a0d6f078d1 <a PropertyDescriptor with map 0x313273311621>,Z=0xf2e91f04231 <true>)
    3: defineProperty [native v8natives.js:779] [pc=0xbe5f...

FATAL ERROR: CALL_AND_RETRY_LAST Allocation failed - process out of memory
```
解决方法：重新执行编译命令，如开发环境下执行<code>npm start</code>

3.端口被占用
```
events.js:154
      throw er; // Unhandled 'error' event
      ^

Error: listen EADDRINUSE 0.0.0.0:3030
    at Object.exports._errnoException (util.js:893:11)
    at exports._exceptionWithHostPort (util.js:916:20)
    at Server.__dirname.Server.Server._listen2 (net.js:1246:14)
    at listen (net.js:1282:10)
    at net.js:1391:9
    at _combinedTickCallback (internal/process/next_tick.js:77:11)
    at process._tickDomainCallback (internal/process/next_tick.js:122:9)
    at Function.Module.runMain (module.js:449:11)
    at /Users/ketu/Sites/edudemo/node_modules/.6.18.0@babel-cli/lib/_babel-node.js:159:24
    at Object.<anonymous> (/Users/ketu/Sites/edudemo/node_modules/.6.18.0@babel-cli/lib/_babel-node.js:160:7)
    at Module._compile (module.js:413:34)
    at Object.Module._extensions..js (module.js:422:10)
    at Module.load (module.js:357:32)
    at Function.Module._load (module.js:314:12)
    at Function.Module.runMain (module.js:447:10)
    at startup (node.js:148:18)
```
解决方法：该错误表明你已经开启了一个端口号为3030的服务，需要先把那个服务关掉


4. 设备上没有剩余空间

报错信息：

```
watch ...  ENOSPC
```
解决方法： 在控制台输入

```
echo fs.inotify.max_user_watches=524288 | sudo tee -a /etc/sysctl.conf && sudo sysctl -p
```



