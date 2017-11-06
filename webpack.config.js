module.exports = {
  output: {
    path: 'web/static-dist/',       // 用于生产环境下的输出目录
    publicPath: '/static-dist/',    // 用于开发环境下的输出目录
  },
  libs: {
    'base': ['libs/base.js'], //可以是一个js文件,
    'vendor': ['libs/vendor.js'], // 即将废弃,请使用base.js
    'html5shiv': ['html5shiv'],
    'fix-ie': ['console-polyfill', 'respond-js'], //也可以是一个npm依赖包
    'jquery-insertAtCaret': ['libs/jquery-insertAtCaret.js'],
    'jquery-nouislider': ['libs/jquery-nouislider.js'],
    'jquery-sortable': ['jquery-sortable'],
    'swiper':['swiper'],
    'perfect-scrollbar': ['libs/perfect-scrollbar/index.js'],
    'jquery-validation': ['libs/jquery-validation.js'],
    'jquery-intro': ['libs/jquery-intro/index.js'],
    'bootstrap-datetimepicker': ['libs/datetimepicker/index.js'],
    'bootstrap-daterangepicker': ['libs/bootstrap-daterangepicker.js'],
    'iframe-resizer': ['libs/iframe-resizer.js'],
    'iframe-resizer-contentWindow': ['libs/iframe-resizer-contentWindow.js'],
    'jquery-timer': ['libs/jquery-timer.js'],
    'jquery-countdown': ['libs/jquery-countdown.js'],
    'jquery-cycle2': ['jquery-cycle2'],
    'excanvas-compiled': ['libs/excanvas-compiled.js'],
    'echo-js': ['echo-js'],
    'jquery-blurr':['jquery-blurr'],
    'jquery-waypoints': ['jquery-waypoints'],
    'jquery-raty': ['libs/jquery-raty.js'],
    'echarts': ['echarts'],
    'select2': ['libs/select2/index.js'],
    'handlebars': ['handlebars']
  },
  noParseDeps: [ // 不解析依赖，加快编译速度
    'jquery/dist/jquery.js',
    'bootstrap/dist/js/bootstrap.js',
    'jquery-validation/dist/jquery.validate.js',
    'perfect-scrollbar/dist/js/perfect-scrollbar.jquery.js',
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
    'echarts/dist/echarts.js',
    'handlebars/dist/handlebars.min.js'
  ],  
  onlyCopys: [ //纯拷贝文件到输出的libs目录下
    {
      name: 'es-ckeditor',
      ignore: [
        '**/samples/**',
        '**/kityformula/libs/**',
      ]
    },
    {
      name: 'es5-shim/es5-shim.js',
    },
    {
      name: 'es5-shim/es5-sham.js',
    },
    {
      name: 'easy-pie-chart/dist/jquery.easypiechart.js',
    }
  ],
  vendorName: 'libs/base,libs/vendor',
  extryCssName: '{main,header,bootstrap,mobile,admin}',
}

