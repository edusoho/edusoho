define(function (require, exports, module) {
    var ThreadShowWidget = require('../thread/thread-show.js');
    var Notify = require('common/bootstrap-notify');
    var Cookie = require('cookie');
    var main = {
        init: function () {
            main.onClickThumb();
            main.onClickfavorite();
            main.onMouseoverQrcode();
            main.onClickHeader();
        },
        onClickThumb: function () {
            $('.js-like-num').on('click', function () {
                var self = $(this);

                var isLiked = self.parent().hasClass('active');
                var url, action;
                if (isLiked) {
                    url = self.data('cancelLikeUrl');
                    action = 'removeClass';
                } else {
                    url = self.data('likeUrl');
                    action = 'addClass';
                }

                $.post(url, function (res) {
                    self.parent().next().html(res.number);
                    self.parent()[action]('active');
                });
            })
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

                $.post(url, function () {
                    self.parent().next().html(text);
                    self.parent()[action]('active');
                })
            })
        },
        onMouseoverQrcode: function () {
            $('.js-qrcode').on('mouseover', function () {
                var $self = $(this);
                var qrcodeUrl = $(this).data('url');

                $.post(qrcodeUrl, function (response) {
                    $self.find('img').attr('src', response.img);
                })
            })
        },
        onClickHeader: function () {
            $('.tab-header').on('click', function () {
                var $this = $(this);
                var index = $this.index();
                $this.addClass('active').siblings().removeClass('active');
                $('#content').find('ul').eq(index).show().siblings().hide();
            });
        }

    }
    exports.run = function () {
        main.init();
        if (!$('#open-course-comment').find('[type=submit]').hasClass('disabled')) {
            var threadShowWidget = new ThreadShowWidget({
                element: '#open-course-comment',
            });
        }
        if (!Cookie.get("uv")) {
            Cookie.set("uv", $("#uv").val(),{path: '/'});
        }

    };
});