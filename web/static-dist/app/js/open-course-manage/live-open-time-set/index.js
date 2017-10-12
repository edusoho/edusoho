webpackJsonp(["app/js/open-course-manage/live-open-time-set/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	var $content = $("#live-lesson-content-field");
	var $form = $('#live-open-course-form');
	var now = new Date();
	var $btn = $('#live-open-course-form-btn');
	var thisTime = $('[name=startTime]').val();
	thisTime = thisTime.replace(/-/g, "/");
	thisTime = Date.parse(thisTime) / 1000;
	var nowTime = Date.parse(new Date()) / 1000;
	
	if (nowTime > thisTime) {
	  $('[name=startTime]').attr('disabled', true);
	  $('#live-length-field').attr('disabled', true);
	  $('#live-open-course-form-btn').attr('disabled', true);
	
	  $('#starttime-help-block').html("直播已经开始或者结束,无法编辑");
	  $('#starttime-help-block').css('color', '#a94442');
	  $('#timelength-help-block').html("直播已经开始或者结束,无法编辑");
	  $('#timelength-help-block').css('color', '#a94442');
	} else {
	  $('[name=startTime]').attr('disabled', false);
	  $('#live-open-course-form-btn').attr('disabled', false);
	}
	
	var validator = $form.validate({
	  rules: {
	    startTime: {
	      required: true,
	      after_now: true
	    },
	    timeLength: {
	      required: true,
	      positive_integer: true,
	      es_remote: {
	        type: 'get',
	        data: { //要传递的数据
	          startTime: function startTime() {
	            return $('[name=startTime]').val();
	          },
	          length: function length() {
	            return $('[name=timeLength]').val();
	          }
	        }
	      }
	    }
	  }
	});
	
	$("[name=startTime]").datetimepicker({
	  autoclose: true,
	  language: document.documentElement.lang
	}).on('hide', function (ev) {
	  $form.validate('[name=startTime]');
	});
	$('[name=startTime]').datetimepicker('setStartDate', now);
	
	$btn.click(function () {
	  if (validator.form()) {
	    $btn.button('loading');
	    $form.submit();
	  }
	});

/***/ })
]);
//# sourceMappingURL=index.js.map