module.exports = {
  output: {
    path: 'web/static-dist/', // 用于生产环境下的输出目录
    publicPath: '/static-dist/', // 用于开发环境下的输出目录
  },
  libs: {
    'base': ['libs/base.js'], // 基础类库
    'boot_base': ['libs/boot_base.js'],
    'jquery-insertAtCaret': ['libs/jquery-insertAtCaret.js'],
    'jquery-nouislider': ['libs/jquery-nouislider.js'],
    'jquery-sortable': ['es-jquery-sortable'],
    'swiper': ['swiper'],
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
    'jquery-blurr': ['jquery-blurr'],
    'jquery-waypoints': ['jquery-waypoints'],
    'jquery-raty': ['libs/jquery-raty.js'],
    'echarts': ['echarts'],
    'select2': ['libs/select2/index.js'],
    'handlebars': ['handlebars'],
    'fullcalendar': ['libs/fullcalendar/index.js'],
    'ltc-sdk-client': ['libs/ltc-sdk-client/index.js'],
    'ltc-sdk-server': ['libs/ltc-sdk-server/index.js'],
    'bootstrap-treeview': ['libs/bootstrap-treeview/index.js'],
    'province-city-area': ['libs/province-city-area.js'],
    'vue': ['libs/vue.js'],
  },
  noParseDeps: { // 不解析依赖，加快编译速度
    'jquery': 'jquery/dist/jquery.js',
    'bootstrap': 'bootstrap/dist/js/bootstrap.js',
    'jquery-validation': 'jquery-validation/dist/jquery.validate.js',
    'perfect-scrollbar': 'perfect-scrollbar/dist/js/perfect-scrollbar.jquery.js',
    'bootstrap-notify': 'bootstrap-notify/bootstrap-notify.js',
    'store': 'store/store.js',
    'bootstrap-daterangepicker': 'bootstrap-daterangepicker/daterangepicker.js',
    'bootstrap-datetime-picker': 'bootstrap-datetime-picker/js/bootstrap-datetimepicker.js',
    'jquery-sortable': 'es-jquery-sortable/source/js/jquery-sortable.js',
    'jquery-cycle2': 'jquery.cycle2/src/jquery.cycle2.min.js',
    'nouislider': 'nouislider/distribute/nouislider.js',
    'echo-js': 'echo-js/dist/echo.js',
    'jquery-waypoints': 'jquery-waypoints/waypoints.js',
    'echarts': 'echarts/dist/echarts.js',
    'handlebars': 'handlebars/dist/handlebars.min.js',
    'moment': 'moment/moment.js',
    'fullcalendar': 'es-fullcalendar/dist/fullcalendar.js',
    'codeages-design': 'codeages-design/dist/codeages-design.js',
  },
  onlyCopys: [ //纯拷贝文件到输出的libs目录下
    {
      name: 'es-ckeditor',
      ignore: [
        '**/samples/**',
        '**/kityformula/libs/**',
      ]
    },
    {
      name: 'swagger-ui/dist/swagger-ui.css',
    },
    {
      name: 'easy-pie-chart/dist/jquery.easypiechart.js',
    },
    {
      name: 'jquery/dist/jquery.min.js',
    },
    {
      name: 'codeages-design',
      ignore: [
        'node_modules/**',
        'src/**',
      ]
    },
    {
      name: 'jquery-validation/dist/jquery.validate.js'
    },
    {
      name: 'bootstrap',
      ignore: [
        'grunt/**',
        'js/**',
        'less/**',
      ]
    },
    {
      from: 'node_modules/@codeages/math-editor/dist/iframe',
      to: 'web/static-dist/libs/math-editor'
    },
  ],
  extryCssName: '{main,header,bootstrap,mobile,admin,item-bank}',
  isESlint: false,
  baseName: 'libs/base,libs/ltc-sdk',
  activitiesDir: 'web/activities',
};