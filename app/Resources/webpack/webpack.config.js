// webpack配置文件
/* 默认值,可在config中重写，但一般不推荐修改
  let defaultOptions = {
    commonsChunkFilename: 'common',
    entryMainname: 'main',
    entryFileName: 'index',

    libsName: 'libs',
    
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
  }
*/

const config = {
  output: {
    path: 'web/static-dist/',       // 用于生产环境下的输出目录
    publicPath: '/static-dist/',    // 用于开发环境下的输出目录
  },
  libs: {
    vendor: ['libs/vendor.js'], //can be a js file
    "fix-ie": ['html5shiv', 'respond-js'],
    "jquery-validation": ['libs/js/jquery-validation.js'],
    "jquery-form": ['jquery-form'],
    'bootstrap-datetimepicker':['libs/js/bootstrap-datetimepicker.js'],
    "perfect-scrollbar":['perfect-scrollbar'],
    "jquery-sortable":['jquery-sortable'],
    "iframe-resizer":['libs/js/iframe-resizer.js'],
    "iframe-resizer-contentWindow":['libs/js/iframe-resizer-contentWindow.js'],
    "es-webuploader":['libs/js/es-webuploader.js'],
    "es-image-crop":['libs/js/es-image-crop.js'],
    "easy-pie-chart":['libs/js/easy-pie-chart.js'],
    "jquery-nstslider":['jquery-nstslider'],
    'jquery-timer':['libs/js/jquery-timer.js'],
  },
  noParseDeps: [ //these node modules will use a dist version to speed up compilation
    'jquery/dist/jquery.js',
    'bootstrap/dist/js/bootstrap.js',
    'admin-lte/dist/js/app.js',
    'jquery-validation/dist/jquery.validate.js',
    'perfect-scrollbar/dist/js/perfect-scrollbar.jquery.js',
    'jquery-form/jquery.form.js',
    'bootstrap-notify/bootstrap-notify.js',
    'store/store.js',
    // The `.` will auto be replaced to `-` for compatibility 
    'respond.js/dest/respond.src.js',
    'bootstrap-datetime-picker/js/bootstrap-datetimepicker.js',
    'jquery-sortable/source/js/jquery-sortable.js',
    'jquery-nstslider/dist/jquery.nstSlider.js',
  ],
  onlyCopys: [
    {
      name: 'es-ckeditor',
      ignore: [
        '**/samples/**',
        // '**/lang/!(zh-cn.js)',
      ]
    },
    {
      name: 'easy-pie-chart',
      ignore: [
        '**/demo/**',
        '**/docs/**',
        '**/src/**',
        '**/test/**',
        '**/dist/angular.easypiechart.js',
        '**/dist/angular.easypiechart.min.js',
        '**/dist/easypiechart.js',
        '**/dist/easypiechart.min.js',
        '.editorconfig',
        '.npmignore',
        '.travis.yml',
        'bower.json',
        'Gruntfile.js',
        'changelog.md',
        'karma.conf.coffee',
        'LICENSE',
        'package.js',
        'package.json',
        'Readme.md',
      ]
    }
  ]
}

export default config;