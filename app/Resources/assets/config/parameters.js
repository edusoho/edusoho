/**
 * todo
 * 1. Is it better to delete `registeredBundles` by auto scan the `src` dir?
 * 2. How to specify the plugin's output path? or the same with system bundles? And also how to construct assets folders for themes ?
 * 3. How to name the output path folder and source code folder in each bundle?
 */
const parameters = {
  registeredBundles: [ //register php bundles
    // 'src/Topxia/WebBundle',
    'plugins/CrmPlugin'
    // 'src/Topxia/AdminBundle',

  ],
  output: {
    path : '../../../../web/build', //file output path, relative to this file
    publicPath: '/build/' //relative to website domain
  },
  libs: {
    vendor: ['../libs/vendor.js'], //can be a js file
    "fix-ie": ['html5shiv', 'respond-js'],
    "jquery-validation": ['../libs/js/jquery-validation.js'],
    "jquery-insertAtCaret": ['../libs/js/jquery-insertAtCaret.js'],
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
  onlyCopys: [
    {
      name: 'es-ckeditor',
      ignore: [
        '**/samples/**',
        // '**/lang/!(zh-cn.js)',
      ]
    }
  ]
}

export default parameters;