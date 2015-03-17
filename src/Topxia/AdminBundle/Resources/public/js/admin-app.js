define(function(require, exports, module) {
	window.$ = window.jQuery = require('jquery');

	require('placeholder');

	require('bootstrap');
	require('common/bootstrap-modal-hack2');

	$('[data-toggle="tooltip"]').tooltip();
	exports.load = function(name) {
		if (window.app.jsPaths[name.split('/', 1)[0]] == undefined) {
			name = window.app.basePath + '/bundles/topxiaadmin/js/controller/' + name;
		}

		seajs.use(name, function(module) {
			if ($.isFunction(module.run)) {
				module.run();
			}
		});

	};

	$('.collapse').on('click','.collect',function() {
		
		var title = $(document).attr("title");
		
		var url = window.location.pathname;
		
		$.post($(this).data('url'),{title:title,url:url},function(data) {
			
			if (data !="error") {
				$('.collect-list').append(data);
			}

			$('.collect').html('<a ><i class="glyphicon glyphicon-ok"></i> 当前页面以添加</a>');
			$('.admin-collect').addClass('open');

		});

	});

	$('.collapse').on('click','.remove',function(){

		$.post($(this).data('url'),{url:window.location.pathname},function(data) {

			$('.collect-list').html(data);
			
			$('.admin-collect').addClass('open');
		});
	});

	window.app.load = exports.load;

	if (app.controller) {
		exports.load(app.controller);
	}

	$(document).ajaxSend(function(a, b, c) {
		if (c.type == 'POST') {
			b.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
		}
	});

});