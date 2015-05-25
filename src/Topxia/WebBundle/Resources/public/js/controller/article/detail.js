define(function(require, exports, module) {

    var ThreadShowWidget = require('../thread/thread-show.js');
    
    exports.run = function() {

        var threadShowWidget = new ThreadShowWidget({
            element: '#detail-content'
        });

        threadShowWidget.element.on('click', '.js-like', function() {
            var $self = $(this);
            if ($self.hasClass('color-p')) {
                $.post($self.data('cancelLikeUrl'), function(note) {
                    $self.find('.js-like-num').html(note.likeNum);
                }).always(function(){
                    $self.removeClass('color-p');
                    $self .closest('.icon-favour').removeClass('active');
                });
            } else {
                $.post($self.data('likeUrl'), function(note) {
                    $self.find('.js-like-num').html(note.likeNum);
                }).always(function(){
                    $self.addClass('color-p');
                    $self.closest('.icon-favour').addClass('active');
                });
                      
            }
      });


    };

});