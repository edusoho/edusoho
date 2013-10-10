

$('.header-nav .item').hover(function() {
	$(this).addClass('item-menus-open');
}, function() {
	$(this).removeClass('item-menus-open');
});

$('#user-nav-item-wrapper').hover(function(){
	$(this).addClass('user-nav-item-open');
}, function(){
	$(this).removeClass('user-nav-item-open');
});

$("#reg-guide").hide();
$("#go-top").hide();

$(function() {

	if ($.browser.msie && parseInt($.browser.version) == 8) {
		var maxWidth = $(".narrow-editor-content img").css('max-width');
		$(".narrow-editor-content img").each(function(index) {
			var width = $(this).width();
			if (width >= parseInt(maxWidth)) {
				$(this).css('width', maxWidth);
			}
		});
	}

	if ($.browser.msie && $.browser.version < 8) {
		$("#not-support-old-ie").slideDown();
	}

	if (!$.browser.msie || $.browser.version > 6) {
		$(window).scroll(function() {
			var scrollTop = $(window).scrollTop();
			if ( scrollTop >= 150) {
				$("#go-top").fadeIn(500);
				if ((SITE_CONFIG.loginHintMode == 'guide') && ($.cookie('loginHintMode') != 'dialog') ) {
					$("#reg-guide").fadeIn(500);
				}
			} else {
				$("#go-top").fadeOut(500);
				if ((SITE_CONFIG.loginHintMode == 'guide') && ($.cookie('loginHintMode') != 'dialog')) {
					$("#reg-guide").fadeOut(500);
				}
			}

		});
		$("#go-top").click(function(a) {
			a.preventDefault();
			window.scroll(0, 0);
			return false;
		});

		$('div#top-adv').find('.close-adv').click(function(e){
			$('#top-adv').hide();
			var option = new Object();
			option['path'] = '/';
			$.cookie('hiddentop', true, option);
		});

		if(!$.cookie('hiddentop')){
			$('#top-adv').show();
		}
	}

	$(document).on('auth.login',  function(){
		alert('popout login dialog');
	});

	$(document).ajaxError(function(event, jqxhr, settings, exception) {
		if (exception == 'Unauthorized') {
			$(document).trigger('auth.login');
		}
	});

	if ((SITE_CONFIG.loginHintMode == 'dialog' || $.cookie('loginHintMode') == 'dialog') ) {
		$(document).trigger('auth.login');
	}

});
