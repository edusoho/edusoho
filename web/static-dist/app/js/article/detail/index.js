webpackJsonp(["app/js/article/detail/index"],[
/* 0 */
/***/ (function(module, exports) {

	import ThreadShowWidget from 'app/js/thread/thread-show';
	
	var element = '#detail-content';
	
	var threadShowWidget = new ThreadShowWidget({
	    element: element
	});
	
	$(element).on('click', '.js-article-like', function () {
	    var $self = $(this);
	    if ($self.hasClass('color-primary')) {
	        $.post($self.data('cancelLikeUrl'), function (article) {
	            $('.article-content').find('.js-like-num').html(article.upsNum);
	        }).always(function () {
	            $self.removeClass('color-primary');
	            $self.closest('.icon-favour').removeClass('active');
	        });
	    } else {
	        $.post($self.data('likeUrl'), function (article) {
	            $('.article-content').find('.js-like-num').html(article.upsNum);
	        }).always(function () {
	            $self.addClass('color-primary');
	            $self.closest('.icon-favour').addClass('active');
	        });
	    }
	});

/***/ })
]);