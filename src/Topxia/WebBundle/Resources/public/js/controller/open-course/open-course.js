define(function(require, exports, module) {
    var ThreadShowWidget = require('../thread/thread-show.js');
    var Notify = require('common/bootstrap-notify');
    var Cookie = require('cookie');
    var main = {
        init: function() {
            main.onClickThumb();
            main.onClickfavorite();
            main.onClickHeader();
        },
        onClickThumb: function() {
            $('.js-like-num').on('click', function() {
                var self = $(this);
                var url, action;
                self.off('click').css('cursor','default');
                url = self.data('likeUrl');
                action = 'addClass';
                $.post(url, function(res) {
                    var $number = self.parent().next();
                    var currentNum = $number.html();

                    $number.html(parseInt(currentNum) + 1);

                    self.parent()[action]('active');
                });
            })
        },
        onClickfavorite: function() {
            $('.js-favorite-num').on('click', function() {
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

                $.post(url, function(data) {
                    if (data['result']) {
                        self.parent().next().html(text);
                        self.parent()[action]('active');
                    } else if (!data['result'] && data['message'] == 'Access Denied') {
                        $('#modal').html();
                        $('#modal').load(self.data('loginUrl'));
                        $('#modal').modal('show');
                    } else {
                        Notify.danger(data['message']);
                    }

                })
            })
        },
        onClickHeader: function() {
            $('.tab-header').on('click', function() {
                var $this = $(this);
                var index = $this.index();
                $this.addClass('active').siblings().removeClass('active');
                $('#content').find('ul').eq(index).show().siblings().hide();
            });
        }

    }
    exports.run = function() {
        main.init();
        if (!$('#open-course-comment').find('[type=submit]').hasClass('disabled')) {
            var threadShowWidget = new ThreadShowWidget({
                element: '#open-course-comment',
            });
        }
    };
});