// 配置文件

/* 默认值,可在options中重写
* defaultOptions = {
    commonsChunkFilename: 'common', 
    bundleMainname: 'main',
    entryFileName: 'index',
    registeredBundles: [ // default auto forEach
      'plugins/CrmPlugin'
      'src/WebBundle',
    ],
    registeredDirs: ['src','plugins'],

    globalAssetsDir: 'app/Resources/assets',
    srcDir: 'src',
    pluginsDir: 'plugins',
    nodeModulesDir: 'node_modules',
    libsDir: 'app/Resources/assets/libs',
    commonDir: 'app/Resources/assets/common',

    assetsDir: 'Resources/assets',
    buildDir: 'Resources/build',

    libsDevOutputDir: 'libs',
    libsBuildOutputDir: 'web/build/libs',

    port: 3030
  }
*/

const options = {
  output: {
    path: 'web/build/', //file output path, relative to this file
    buildpath: './',
    publicPath: '/build/' //relative to website domain
  },
  libs: {
    vendor: ['libs/vendor.js'], //can be a js file
    "fix-ie": ['html5shiv', 'respond-js'], //can be a node_modules package
    "jquery-validation": ['libs/js/jquery-validation.js'],
    "jquery-insertAtCaret": ['libs/js/jquery-insertAtCaret.js'],
    "jquery-form": ['jquery-form'],
  },
  noParseDeps: [ //these node modules will use a dist version to speed up compilation
    'jquery/dist/jquery.js',
    'bootstrap/dist/js/bootstrap.js',
    'admin-lte/dist/js/app.js',
    'jquery-validation/dist/jquery.validate.js',
    'jquery-form/jquery.form.js',
    'bootstrap-notify/bootstrap-notify.js',
    // The `.` will auto be replaced to `-` for compatibility 
    'respond.js/dest/respond.src.js',
    'bootstrap-daterangepicker/daterangepicker.js',
    'moment/moment.js',
  ],
  onlyCopys: [ // copy these form node modules to libs dir 
    {
      name: 'es-ckeditor',
      ignore: [
        '**/samples/**',
        // '**/lang/!(zh-cn.js)',
      ]
    }
  ],
}

export default options;