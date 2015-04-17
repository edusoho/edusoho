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
		var param = window.location.search;
		
		$.post($(this).data('url'),{title:title,url:url+param},function(data) {
			
			if (data !="error") {
				$('.collect-list').append(data);
			}

			if($('.divider').length> 0) {

				$('.collect').html('<a ><i class="glyphicon glyphicon-ok"></i> 当前页面已添加</a>');
				
			}else{

				$('.collect').html('<a ><i class="glyphicon glyphicon-ok"></i> 当前页面已添加</a><li role="presentation" class="divider"></li>');
			
			}
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

    if (app.scheduleCrontab) {
        $.post(app.scheduleCrontab);
    }	

});