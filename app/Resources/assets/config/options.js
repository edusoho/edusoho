// 配置文件

const options = {
  // registeredBundles: [ //register php bundles
  //   // 'src/Topxia/WebBundle',
  //   'plugins/CrmPlugin'
  //   // 'src/Topxia/AdminBundle',
  // ],
  // currentPath: __dirname,
  output: {
    path: 'web/build', //file output path, relative to this file
    buildpath: './',
    publicPath: '/build/' //relative to website domain
  },
  libs: {
    vendor: ['libs/vendor.js'], //can be a js file
    "fix-ie": ['html5shiv', 'respond-js'],
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
  // defaultValue 'common'
  commonsChunkFilename: 'common',
  // defaultValue 'main'
  // bundleMainname: 'main'
  port: 3030
}

export default options;