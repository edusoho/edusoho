import ThreadShowWidget from 'app/js/thread/thread-show';
import notify from 'common/notify';

const main = {
	init: function () {
		main.onClickThumb();
		main.onClickfavorite();
		main.removeMask();
		main.onClickReplay();
	},
	onClickThumb: function () {
		$('.js-like-num').on('click', function () {
			var self = $(this);
			var url, action;
			self.off('click').css('cursor', 'default');
			url = self.data('likeUrl');
			action = 'addClass';
			$.post(url, function (res) {
				var $number = self.parent().next();
				var currentNum = $number.html();

				$number.html(parseInt(currentNum) + 1);

				self.parent()[action]('active');
			});
		});
	},
	onClickfavorite: function () {
		$('.js-favorite-num').on('click', function () {
			var self = $(this);

			var isFavorited = self.parent().hasClass('active');
			var url, action, text;
			if (isFavorited) {
				text = '收藏';
				url = self.data('cancelFavoriteUrl');
				action = 'removeClass';
			} else {
				url = self.data('favoriteUrl');
				action = 'addClass';
				text = '已收藏';
			}

			$.post(url, function (data) {
				if (data['result']) {
					self.parent().next().html(text);
					self.parent()[action]('active');
				} else if (!data['result'] && data['message'] == 'Access Denied') {
					$('#modal').html();
					$('#modal').load(self.data('loginUrl'));
					$('#modal').modal('show');
				} else {
					notify('danger',data['message']);
				}

			});
		});
	},
	//点击ES直播公开课回放
	onClickReplay: function () {
		$('.js-play-es-live').on('click', function () {
			var replayUrl = $(this).data('url');
			var html = '<iframe src=\'' + replayUrl + '\' name=\'viewerIframe\' id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'100%\' style=\'border:0px\'></iframe>';
			$('.open-course-views').html(html);
		});
	},
	isEsVedio: function () {
		if ($('#lesson-preview-player').html() == '') {
			$('.embed-responsive-16by9').addClass('masks');
		}
	},
	removeMask: function () {
		setTimeout(main.isEsVedio, 1500);
	}
};

main.init();

if (!$('#open-course-comment').find('[type=submit]').hasClass('disabled')) {
	var threadShowWidget = new ThreadShowWidget({
		element: '#open-course-comment',
	});
}
