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
    'fetch-ie8/fetch.js',
    'console-polyfill/index.js',
    'html5shiv/dist/html5shiv.js',
    'bootstrap-daterangepicker/daterangepicker.js',
    'moment/moment.js',
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
      name: 'font-awesome/css/font-awesome.css'
    },
    {
      name: 'es5-shim/es5-shim.js'
    },
    {
      name: 'es5-shim/es5-sham.js'
    }
  ],
}

export default config;