define(function(require, exports, module) {
	window.$ = window.jQuery = require('jquery');
	require('bootstrap');
	require('common/bootstrap-modal-hack2');

	exports.load = function(name) {
		if (window.app.jsPaths[name.split('/', 1)[0]] == undefined) {
			name = window.app.basePath + '/bundles/topxiaweb/js/controller/' + name;
		}

		seajs.use(name, function(module) {
			if ($.isFunction(module.run)) {
				module.run();
			}
		});

	};

	exports.loadScript = function(scripts) {
		for(var index in scripts) {
			exports.load(scripts[index]);
		}
		
	}

	window.app.load = exports.load;

	if (app.themeGlobalScript) {
		exports.load(app.themeGlobalScript);
	}

	if (app.controller) {
		exports.load(app.controller);
	}

	if (app.scripts) {
		exports.loadScript(app.scripts);
	}

	$(document).ajaxError(function(event, jqxhr, settings, exception) {
		var json = jQuery.parseJSON(jqxhr.responseText);
			error = json.error;
		if (!error) {
			return ;
		}

		if (error.name == 'Unlogin') {
			$('.modal').modal('hide');

			$("#login-modal").modal('show');
			$.get($('#login-modal').data('url'), function(html){
				$("#login-modal").html(html);
			});
		}
	});

	if ($('html').hasClass('lt-ie8')) {
		var message = '<div class="alert alert-warning" style="margin-bottom:0;text-align:center;">';
		message += '您的浏览器版本太低，不能正常使用本站，请使用';
		message += '<a href="http://windows.microsoft.com/zh-CN/internet-explorer/downloads/ie" target="_blank">IE8浏览器</a>、';
		message += '<a href="http://www.baidu.com/s?wd=%E8%B0%B7%E6%AD%8C%E6%B5%8F%E8%A7%88%E5%99%A8" target="_blank">谷歌浏览器</a><strong>(推荐)</strong>、';
		message += '<a href="http://firefox.com.cn/download/" target="_blank">Firefox浏览器</a>，访问本站。';
		message += '</div>';

		$('body').prepend(message);
	}

	$( document ).ajaxSend(function(a, b, c) {
		if (c.type == 'POST') {
			b.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
		}
	});


    floatConsult();

    function floatConsult()
    {
        var $element = $('#float-consult');
        if ($element.length == 0) {
            return ;
        }

        if ($element.data('display') == 'off') {
            return ;
        }

        var marginTop = (0 - $element.height() / 2) + 'px' ;

        var isIE10 = /MSIE\s+10.0/i.test(navigator.userAgent)
	    && (function() {"use strict";return this === undefined;}());

	    var isIE11 = (/Trident\/7\./).test(navigator.userAgent);

    	if (isIE10 || isIE11) {
	        $element.css( {marginTop: marginTop, visibility: 'visible',marginRight:'16px'});
    	} else {
	        $element.css( {marginTop: marginTop, visibility: 'visible'});
    	}

        $element.find('.btn-group-vertical .btn').popover({
            placement: 'left',
            trigger: 'hover',
            html: true,
            content: function() {
                return $($(this).data('contentElement')).html();
            }
        });
    }

    $("i.hover-spin").mouseenter(function() {
    	$(this).addClass("md-spin");
    }).mouseleave(function() {
    	$(this).removeClass("md-spin");
    });


});