// 配置文件

/* 默认值,可在options中重写
  let defaultOptions = {
    commonsChunkFilename: 'common',
    entryMainname: 'main',
    entryFileName: 'index',

    libsName: 'libs',

    pluginsName: 'plugins',  //可以是数组,指定监听具体的插件，如 ['plugins/CrmPlugin','plugins/VipPlugin']
    pluginAssetsDir: 'Resources/static-src',

    appName: 'app',
    appIgnores: ['admin'],
    appAssetsDir: 'app',

    adminName: 'admin',
    adminAssetsDir: 'app/js/admin',

    globalDir: 'app/Resources/static-src',
    libsDir: 'app/Resources/static-src/libs',
    commonDir: 'app/Resources/static-src/common',
    nodeModulesDir: 'node_modules',

    fontlimit: 20480,
    imglimit: 10240,
    fontName: 'fonts',
    imgName: 'img',

    onlyCopys: [],

    devtool: 'cheap-module-eval-source-map', // 可设置为 'source-map'，方便错误排查 

    openModule: ['lib','app','admin','plugin','copy'], // 可以选择监听哪几种资源文件
  }
*/

const options = {
  output: {
    path: 'web/static-dist/',       // 用于生产环境下的输出目录
    publicPath: '/static-dist/',    // 用于开发环境下的输出目录
  },
  libs: {
    vendor: ['libs/vendor.js'], //可以是一个js文件
    "fix-ie": ['html5shiv', 'respond-js'], //也可以是一个npm依赖包
    "jquery-validation": ['libs/js/jquery-validation.js'],
    "jquery-insertAtCaret": ['libs/js/jquery-insertAtCaret.js'],
    "jquery-form": ['jquery-form'],
  },
  noParseDeps: [ //使用一个dist版本加快编译速度
    'jquery/dist/jquery.js',
    'bootstrap/dist/js/bootstrap.js',
    // 'admin-lte/dist/js/app.js',
    'jquery-validation/dist/jquery.validate.js',
    'jquery-form/jquery.form.js',
    'bootstrap-notify/bootstrap-notify.js',
    // The `.` will auto be replaced to `-` for compatibility 
    'respond.js/dest/respond.src.js',
    'bootstrap-daterangepicker/daterangepicker.js',
    'moment/moment.js',
  ],
  onlyCopys: [ //纯拷贝文件到输出的libs目录下
    {
      name: 'es-ckeditor',
      ignore: [
        '**/samples/**',
        '**/lang/!(zh-cn.js)',
      ]
    }
  ],
}

export default options;