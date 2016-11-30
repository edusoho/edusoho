/**
 * todo
 * 1. Is it better to delete `registeredBundles` by auto scan the `src` dir?
 * 2. How to specify the plugin's output path? or the same with system bundles? And also how to construct assets folders for themes ?
 * 3. How to name the output path folder and source code folder in each bundle?
 */
const parameters = {
  registeredBundles: [ //register php bundles
    'WebBundle',
  ],
  output: {
    path : '../../../../web/build', //file output path, relative to this file
    publicPath: '/build/' //relative to website domain
  },
  libs: {
    vendor: ['../libs/vendor.js'], //can be a js file
    "fix-ie": ['html5shiv', 'respond-js'],
    "jquery-validation": ['../libs/js/jquery-validation.js'],
    "jquery-form": ['jquery-form'],
    'bootstrap-datetimepicker':['../libs/js/bootstrap-datetimepicker.js'],
    "perfect-scrollbar":['perfect-scrollbar'],
    "jquery-sortable":['jquery-sortable'],
    "iframe-resizer":['../libs/js/iframe-resizer.js'],
    "iframe-resizer-contentWindow":['../libs/js/iframe-resizer-contentWindow.js'],
    "es-webuploader":['../libs/js/es-webuploader.js'],
    "es-image-crop":['../libs/js/es-image-crop.js'],
    "easy-pie-chart":['../libs/js/easy-pie-chart.js'],
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

export default parameters;