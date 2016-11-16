# 前端开发说明

### 本项目采用基于[webpack](../doc/webpack.md)的前端解决方案

### 目录文件说明
    app/Resourses/assets/common    存放整个项目共用的代码片段或组件，在js中import引入  
    app/Resourses/assets/config    存放webpack 打包方案
    app/Resourses/assets/libs      存放整个项目中可单独使用的插件，在twig中引入

    app/Resourses/assets/libs/vendor.js   约定以vendor.js命名为整个项目的全局js
    app/Resourses/assets/libs/vendor.less 约定以vendor.less命名为整个项目的全局样式


    {pulgin path or bundle path}/Resourses/assets/
    存放整个插件或Bundle的具体的业务代码，包括img、less、js

    {pulgin path or bundle path}/Resourses/assets/main.js 
    约定以main.js命名为整个插件或Bundle的全局js

    {pulgin path or bundle path}/Resourses/assets/main.less 
    约定以main.less命名为整个插件或Bundle的全局样式

    {pulgin path or bundle path}/Resourses/assets/js/common/ 
    存放整个插件或Bundle的公用部分js，在页面入口文件中引入

    {pulgin path or bundle path}/Resourses/assets/js/**/index.js 
    约定页面的入口文件统一用index.js命名
