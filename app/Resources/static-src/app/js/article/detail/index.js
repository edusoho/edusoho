import ThreadShowWidget from 'app/js/thread/thread-show';

let element = '#detail-content';

let threadShowWidget = new ThreadShowWidget({
	element: element
});

$(element).on('click', '.js-article-like', function() {
	const $self = $(this);
	if ($self.hasClass('color-primary')) {
		$.post($self.data('cancelLikeUrl'), function(article) {
			$('.article-content').find('.js-like-num').html(article.upsNum);
		}).always(function(){
			$self.removeClass('color-primary');
			$self .closest('.icon-favour').removeClass('active');
		});
	} else {
		$.post($self.data('likeUrl'), function(article) {
			$('.article-content').find('.js-like-num').html(article.upsNum);
		}).always(function(){
			$self.addClass('color-primary');
			$self.closest('.icon-favour').addClass('active');
		});
              
	}
});

