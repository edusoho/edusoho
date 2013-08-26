seajs.config({
	plugins: ['shim', 'style'],

	alias: {
		'jquery': {
			src: 'jquery/jquery-1.9.1.js',
			exports: 'jQuery'
		},
		'$': {
			src: 'jquery/jquery-1.9.1.js',
			exports: 'jQuery'
		},
		'$-debug': {
			src: 'jquery/jquery-1.9.1.js',
			exports: 'jQuery'
		},
		"jquery.form": "jquery-plugin/form/3.36.0/form",
		"jquery.sortable": "jquery-plugin/sortable/0.9.10/sortable.js",
		"jquery.raty": "jquery-plugin/raty/2.5.2/raty",
		"jquery.perfect-scrollbar": "jquery-plugin/perfect-scrollbar/0.4.1/perfect-scrollbar",
		"jquery.select2": "jquery-plugin/select2/3.4.1/select2",
		"jquery.select2-css": "jquery-plugin/select2/3.4.1/select2.css",
		"jquery.jcrop-css": "jquery-plugin/jcrop/0.9.12/jcrop.css",
		'jquery.bootstrap-datetimepicker': "jquery-plugin/bootstrap-datetimepicker/1.0.0/datetimepicker",
		'jquery.toastr': {
			src: 'jquery/toastr/1.0.2/toastr.js',
			deps: ['jquery']
		},
		'jquery.jcrop':  {
			src: 'jquery-plugin/jcrop/0.9.12/jcrop.js',
			deps: ['jquery', 'jquery.jcrop-css']
		},
		'jquery.ui': {
			src: 'jquery/ui/1.10.3/jquery-ui-custom.min.js',
			deps: ['jquery']
		},
		'bootstrap': {
			src: 'bootstrap-rc/3.0.0/bootstrap.js',
			deps: ['jquery']
		},
		'autocomplete': 'arale/autocomplete/1.2.2/autocomplete-debug',
		'upload': 'arale/upload/1.0.1/upload',
		'bootstrap.validator': 'bootstrap/3.0.0/validator',
		'class': 'arale/class/1.1.0/class',
		'base': 'arale/base/1.1.0/base',
		'widget': 'arale/widget/1.1.1/widget',
		'sticky': 'arale/sticky/1.2.1/sticky',
		"templatable": "arale/templatable/0.9.0/templatable",
		'json': 'gallery/json/1.0.2/json',
		"handlebars": "gallery/handlebars/1.0.2/handlebars",
		"backbone": "gallery/backbone/1.0.0/backbone",
		"swfobject": "gallery/swfobject/2.2.0/swfobject.js",
		'ckeditor': 'ckeditor/ckeditor.js',
		'video-js': 'gallery2/video-js/4.0.4/video-js',
		'swfupload': 'gallery2/swfupload/2.2.0/swfupload-debug',
		'edusoho.linkselect': 'edusoho/linkselect/1.0/linkselect-debug',
	},

	// 预加载项
	preload: [
	this.JSON ? '' : 'json'],

	// 路径配置
	paths: {
		'common': 'common/'
	},

	// 变量配置
	vars: {
		'locale': 'zh-cn'
	},

	charset: 'utf-8',

	debug: app.debug
})