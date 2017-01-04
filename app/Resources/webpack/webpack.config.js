// webpack配置文件
const config = {
  output: {
    path: 'web/static-dist/',       // 用于生产环境下的输出目录
    publicPath: '/static-dist/',    // 用于开发环境下的输出目录
  },
  libs: {
    vendor: ['libs/vendor.js'], //可以是一个js文件
    "fix-ie": ['console-polyfill','html5shiv', 'respond-js'], //也可以是一个npm依赖包
    "jquery-validation": ['libs/js/jquery-validation.js'],
    "jquery-insertAtCaret": ['libs/js/jquery-insertAtCaret.js'],
    "jquery-form": ['jquery-form'],
    'bootstrap-datetimepicker':['libs/js/bootstrap-datetimepicker.js'],
    "perfect-scrollbar":['perfect-scrollbar'],
    "jquery-sortable":['jquery-sortable'],
    "iframe-resizer":['libs/js/iframe-resizer.js'],
    "iframe-resizer-contentWindow":['libs/js/iframe-resizer-contentWindow.js'],
    "es-webuploader":['libs/js/es-webuploader.js'],
    "es-image-crop":['libs/js/es-image-crop.js'],
    "easy-pie-chart":['libs/js/easy-pie-chart.js'],
    "jquery-nouislider":['nouislider'],
    'jquery-timer':['libs/js/jquery-timer.js'],
    'jquery-range':['libs/js/jquery-range.js']
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
    'fetch-ie8/fetch.js',
    'console-polyfill/index.js',
    'html5shiv/dist/html5shiv.js',
    'bootstrap-daterangepicker/daterangepicker.js',
    'moment/moment.js',
    'bootstrap-datetime-picker/js/bootstrap-datetimepicker.js',
    'jquery-sortable/source/js/jquery-sortable.js',
    'nouislider/distribute/nouislider.js'
  ],
  onlyCopys: [ //纯拷贝文件到输出的libs目录下
    {
      name: 'es-ckeditor',
      ignore: [
        '**/samples/**',
        '**/lang/!(zh-cn.js)',
        '**/kityformula/libs/**',
        '**/kityformula/kityformula/src/**'
      ]
    },
    {
      name: 'bootstrap/dist/css/bootstrap.css'
    },
    {
      name: 'bootstrap/dist/fonts/'
    },
    {
      name: 'font-awesome/css/font-awesome.css'
    },
    {
      name: 'font-awesome/fonts/'
    },
    {
      name: 'es5-shim/es5-shim.js'
    },
    {
      name: 'es5-shim/es5-sham.js'
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