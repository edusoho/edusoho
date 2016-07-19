define(function(require, exports, module) {
    var ThreadShowWidget = require('../thread/thread-show.js');
    var Notify = require('common/bootstrap-notify');
    var main = {
        init : function(){
            main.clickThumb();
            main.clickfavorite();
            main.mouseoverQrcode();
            main.clickHeader();
        },
        clickThumb : function(){
            $('.js-like-num').on('click',function(){
                var _self = $(this);
                if(_self.parent().hasClass('active')){
                    $.post(_self.data('cancelLikeUrl'),function(res){
                      _self.parent().next().html(res.number);
                      _self.parent().removeClass('active')
                    })
                }else{
                    $.post(_self.data('likeUrl'),function(res){
                        _self.parent().next().html(res.number);
                        _self.parent().addClass('active')
                    })
                }
            })
        },
        clickfavorite : function(){
            $('.js-favorite-num').on('click',function(){
                var _self = $(this);
                if(_self.parent().hasClass('active')){
                    $.post(_self.data('cancelFavoriteUrl'),function(){
                        _self.parent().next().html('收藏');
                        _self.parent().removeClass('active');
                    })
                }else{
                    $.post(_self.data('favoriteUrl'),function(res){
                        _self.parent().next().html('已收藏');
                        _self.parent().addClass('active');
                    })
                }
            })
        },
        mouseoverQrcode : function(){
            $('.js-qrcode').on('mouseover',function(){
                var $self = $(this);
                var qrcodeUrl = $(this).data('url');

                $.post(qrcodeUrl,function(response){
                    $self.find('img').attr('src',response.img);
                })
            })
        },
        clickHeader : function(){
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