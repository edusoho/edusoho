define(function(require, exports, module) {

    var ThreadShowWidget = require('../thread/thread-show.js');
    
    exports.run = function() {

        var threadShowWidget = new ThreadShowWidget({
            element: '#detail-content',
        });

        threadShowWidget.element.on('click', '.js-article-like', function() {
            var $self = $(this);
            if ($self.hasClass('color-primiay')) {
                $.post($self.data('cancelLikeUrl'), function(article) {
                    $('.article-content').find('.js-like-num').html(article.upsNum);
                }).always(function(){
                    $self.removeClass('color-primiay');
                    $self .closest('.icon-favour').removeClass('active');
                });
            } else {
                $.post($self.data('likeUrl'), function(article) {
                    $('.article-content').find('.js-like-num').html(article.upsNum);
                }).always(function(){
                    $self.addClass('color-primiay');
                    $self.closest('.icon-favour').addClass('active');
                });
                      
            }
        });

    };

});