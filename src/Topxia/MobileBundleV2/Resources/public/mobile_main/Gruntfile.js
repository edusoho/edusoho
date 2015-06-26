module.exports = function(grunt) {
    var libPath = "../libs/";
    var jsPath="js/";
	var templuteUrl="view/";
    grunt.initConfig({
    	jsVersion: grunt.file.readJSON('package.json'),
    	concat: {
    		options:{
                separator:';\n'
            },
    	    dist: {
    	      files: {
                  'release/module.all.js':[
                      jsPath+'app.js',
                      jsPath+'service.js',
                      jsPath+'filter.js',
                      jsPath+'directive',
                      jsPath+'factory.js',
                      jsPath+'provider.js'
                  ],
                  'release/controller.all.js':[
                      jsPath+'controller/**.js'
                  ],
                  'release/libs.all.js':[
                      libPath+'angular/1.3.0/angular.min.js',
                      libPath+'angular-ui-router.min.js',
                      libPath+'angular/1.3.0/angular-sanitize.min.js',
                      libPath+'fastclick.js',
                      libPath+'frozen/1.3.0/js/lib/zepto.min.js',
                      libPath+'frozen/1.3.0/js/frozen.js',
                      libPath+'frozen/1.3.0/js/bindonce.min.js'
                  ]
              }
    	    }
    	},
    	cssmin: {
            options: {
                banner: '/* work by TQ UED */',
                rebase:false
            },
  	        compress: {
  			     files: {
  			          'release/all.min.css': [
    					  'css/ui-style.css',
    					  'fonts/iconfont.css',
    					  libPath + 'frozen/1.2.1/css/frozen.css',
                              libPath + 'sideview-component.css'
                      ]
  	             }
            }
  	    },
        uglify: {
            build: {
            	files:{
            		'release/app.all.min.js': ["release/module.all.js","release/controller.all.js"]
            	}
            }
        },
        ngtemplates:  {
            app:        {
                src:      templuteUrl + '**.html',
                dest:     'release/template.js',
                options:    {
                    htmlmin:  {
                        collapseWhitespace: true,
                        collapseBooleanAttributes: true
                    },
                    prefix: '/'
                }
            }
        },
        zip : {
          options:{
                separator:';\n'
            },
          dist: {
            files: {
                  'release/app_Android.zip': [
                      'release/template.js',
                      'release/app.all.min.js',
                      'release/all.min.css',
                      'release/libs.all.js',
                      jsPath + 'cordova_Android.js',
                      jsPath + 'cordova_plugins.js',
                      jsPath + 'plugins/esNativeCore.js'
                  ],
                   'release/app_iOS.zip':[
                      'release/template.js',
                      'release/app.all.min.js',
                      'release/all.min.css',
                      'release/libs.all.js',
                      jsPath + 'cordova_iOS.js',
                      jsPath + 'cordova_plugins.js',
                      jsPath + 'plugins/esNativeCore.js'
                  ]
              }
          }
        }
    });

    grunt.loadNpmTasks('grunt-angular-templates');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('node-zip');
    grunt.registerTask('default', [ 'ngtemplates','concat', 'uglify','cssmin', 'zip']);
    
};