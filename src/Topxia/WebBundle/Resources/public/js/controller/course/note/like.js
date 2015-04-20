define(function(require, exports, module) {

    exports.run = function() {
        var $ul = $('#note-list');

        $ul.on('click', '.short-text', function() {
            var $short = $(this);
            $short.slideUp('fast').parents('.short-long-text').find('.long-text').slideDown('fast');

        });

        $ul.on('click', '.long-text', function() {
            var $long = $(this);
            $long.slideUp('fast').parents('.short-long-text').find('.short-text').slideDown('fast');
        });

        $ul.on('click', '.js-like', function() {
            var $self = $(this);
            if ($(this).hasClass('liked')) {
                $(this).removeClass('liked');
                $.post($self.data('cancelLikeUrl'), function(note) {
                    $self.find('.js-like-num').html(note.likeNum);
                });
            } else {
                $(this).addClass('liked');
                $.post($self.data('likeUrl'), function(note) {
                    $self.find('.js-like-num').html(note.likeNum);
                });
                
            }
        });

    };

});