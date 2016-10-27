## webpack前端方案初始化（需安装nodejs环境）

### 依赖安装

```
npm install
```

### 开发模式

```
npm run dev
npm run dev port:3038 #改变端口
```

```
#此命令默认会绑定到0.0.0.0:3030，但不会生成真实文件，但可以通过http://127.0.0.1:3030/build浏览到文件目录
#本项目中的打包配置文件位于app/Resources/assets/config/parameters.js，
其中output.publicPath指定为'/build/'，
所以开发模式下打包后的前端文件可以通过http://127.0.0.1:3030/build/xxx/xxx.js访问
#开发模式下，需在symfony的配置文件app/config/parameters.yml中配置以下字段:
assets:
    packages:
        webpack:
            base_urls:
                - 'http://127.0.0.1:3030'
```

### 最终编译

```
#会生成实体文件，本项目会生成到web/bundles/
npm run compile
npm run compile:debug  #不压缩
```

### 改进
* dev模式下scanPath的新文件无法监听的问题
* webpack编译失败的通知
* npm run compile 没修改的话也会git变化
* libs和common的区别能不能再改进
* 搞成独立npm
