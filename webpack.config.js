module.exports = {
  output: {
    path: 'web/static-dist/',       // 用于生产环境下的输出目录
    publicPath: '/static-dist/',    // 用于开发环境下的输出目录
  },
  libs: {
    'vendor': ['libs/vendor.js'], //可以是一个js文件,
    'html5shiv': ['html5shiv'],
    'fix-ie': ['console-polyfill', 'respond-js'], //也可以是一个npm依赖包
    'jquery-insertAtCaret': ['libs/js/jquery-insertAtCaret.js'],
    'jquery-form': ['jquery-form'],
    'jquery-nouislider': ['libs/js/jquery-nouislider.js'],
    'jquery-sortable': ['jquery-sortable'],
    'swiper':['swiper'],
    'perfect-scrollbar': ['libs/js/perfect-scrollbar/perfect-scrollbar.js'],
    'jquery-validation': ['libs/js/jquery-validation.js'],
    'jquery-intro': ['libs/js/jquery-intro/jquery-intro.js'],
    'bootstrap-datetimepicker': ['libs/js/bootstrap-datetimepicker.js'],
    'iframe-resizer': ['libs/js/iframe-resizer.js'],
    'iframe-resizer-contentWindow': ['libs/js/iframe-resizer-contentWindow.js'],
    'jquery-timer': ['libs/js/jquery-timer.js'],
    'jquery-countdown': ['libs/js/jquery-countdown.js'],
    'jquery-cycle2': ['jquery-cycle2'],
    'excanvas-compiled': ['libs/js/excanvas-compiled.js'],
    'echo-js': ['echo-js'],
    'jquery-blurr':['jquery-blurr'],
    'jquery-waypoints': ['jquery-waypoints'],
    'jquery-raty': ['libs/js/jquery-raty/jquery-raty.js'],
    'echarts': ['echarts'],
    'select2': ['libs/js/select2.js'],
    
    // 样式
    'app-bootstrap': ['libs/app-bootstrap/less/bootstrap.less']
  },
  noParseDeps: [ // 不解析依赖，加快编译速度
    'jquery/dist/jquery.js',
    'bootstrap/dist/js/bootstrap.js',
    'jquery-validation/dist/jquery.validate.js',
    'perfect-scrollbar/dist/js/perfect-scrollbar.jquery.js',
    'jquery-form/jquery.form.js',
    'bootstrap-notify/bootstrap-notify.js',
    'store/store.js',
    'respond.js/dest/respond.src.js', // '.'会被转换成'-'
    'console-polyfill/index.js',
    'html5shiv/dist/html5shiv.js',
    'bootstrap-daterangepicker/daterangepicker.js',
    'moment/moment.js',
    'bootstrap-datetime-picker/js/bootstrap-datetimepicker.js',
    'jquery-sortable/source/js/jquery-sortable.js',
    'jquery.cycle2/src/jquery.cycle2.min.js',
    'nouislider/distribute/nouislider.js',
    'echo-js/dist/echo.js',
    'jquery-waypoints/waypoints.js',
    'echarts/dist/echarts.js'
  ],
  onlyCopys: [ //纯拷贝文件到输出的libs目录下
    {
      name: 'es-ckeditor',
      ignore: [
        '**/samples/**',
        '**/lang/!(zh-cn.js)',
        '**/kityformula/libs/**',
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

