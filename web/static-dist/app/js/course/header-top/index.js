webpackJsonp(["app/js/course/header-top/index"],{

/***/ "bbe1f1e10924ccc8bdb1":
/***/ (function(module, exports) {

	"use strict";
	
	$("body").on("click", ".es-qrcode", function () {
	  var $this = $(this);
	  if ($this.hasClass('open')) {
	    $this.removeClass('open');
	  } else {
	    $.ajax({
	      type: "post",
	      url: $this.data("url"),
	      dataType: "json",
	      success: function success(data) {
	        $this.find(".qrcode-popover img").attr("src", data.img);
	        $this.addClass('open');
	      }
	    });
	  }
	});

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	__webpack_require__("bbe1f1e10924ccc8bdb1");
	
	var $unfavorite = $('.js-unfavorite-btn');
	var $favorite = $('.js-favorite-btn');
	bindOperation($unfavorite, $favorite);
	bindOperation($favorite, $unfavorite);
	discountCountdown();
	ancelRefund();
	
	function ancelRefund() {
		$(".cancel-refund").on('click', function () {
			if (!confirm(Translator.trans('course_set.refund_cancel_hint'))) {
				return false;
			}
			$.post($(this).data('url'), function (data) {
				window.location.reload();
			});
		});
	}
	
	function bindOperation($needHideBtn, $needShowBtn) {
		$needHideBtn.click(function () {
			var url = $needHideBtn.data('url');
			console.log(url);
			if (!url) {
				return;
			}
			$.post(url).done(function (success) {
				if (!success) return;
				$needShowBtn.show();
				$needHideBtn.hide();
			});
		});
	}
	
	function discountCountdown() {
		var remainTime = parseInt($('#discount-endtime-countdown').data('remaintime'));
		if (remainTime >= 0) {
			var endtime = new Date(new Date().valueOf() + remainTime * 1000);
			$('#discount-endtime-countdown').countdown(endtime, function (event) {
				var $this = $(this).html(event.strftime(Translator.trans('course_set.show.count_down_format_hint')));
			}).on('finish.countdown', function () {
				$(this).html(Translator.trans('course_set.show.time_finish_hint'));
				setTimeout(function () {
					$.post(app.crontab, function () {
						window.location.reload();
					});
				}, 2000);
			});
		}
	}

/***/ })

});
//# sourceMappingURL=index.js.map