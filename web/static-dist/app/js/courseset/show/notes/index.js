webpackJsonp(["app/js/courseset/show/notes/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	$('.js-like').on('click', function (event) {
	  var $self = $(event.currentTarget);
	  var $num = $self.find('.js-like-num');
	  var num = parseInt($num.text());
	  var url = void 0,
	      isLiked = $self.hasClass('color-primary');
	  if (isLiked) {
	    url = $self.data('cancelLikeUrl');
	  } else {
	    url = $self.data('likeUrl');
	  }
	
	  $.post(url).done(function (response) {
	    if (isLiked) {
	      $self.removeClass('color-primary');
	      $num.text(num - 1);
	    } else {
	      $self.addClass('color-primary');
	      $num.text(num + 1);
	    }
	  });
	});
	
	$('#note-list .content').each(function () {
	  var height = $(this).find('.editor-text').height();
	  if (height > 90) {
	    $(this).next().show();
	  }
	});
	
	$('#note-list').on('click', '.js-more-show', function () {
	  $(this).prev().toggleClass('active');
	});

/***/ })
]);
//# sourceMappingURL=index.js.map