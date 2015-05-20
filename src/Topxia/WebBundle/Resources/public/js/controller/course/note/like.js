define(function(require, exports, module) {

    exports.run = function() {
        var $ul = $('#note-list');

        // 笔记
        if($(".note-list .note-item .more").height()) {
            var noteListPShow = $(".note-list .note-item .more");

            if(noteListPShow.hasClass('more-show') ) {
                noteListPShow.data('toggle', true);

            }else {
                noteListPShow.data('toggle', false);
            }

            noteListPShow.each(function(){
                if($(this).siblings("p").height() >= 90) {
                    $(this).show();
                }
            });

            noteListPShow.click(function(){
                var btn = $(this);
                if(btn.data('toggle') && btn.siblings("p").height()) {
                    btn.siblings("p").addClass("active");
                    btn.addClass('more-hidden').removeClass('more-show').text("[收起全文]");
                    btn.data('toggle', false);

                } else {
                    btn.siblings("p").removeClass("active");
                    btn.addClass('more-show').removeClass('more-hidden').text("[展开全文]");
                    btn.data('toggle', true);
                }

            });
        };

        $ul.on('click', '.js-like', function() {
            var $self = $(this);
            var $icon = $(this).find('.es-icon-thumbup'); 
            if ($icon.hasClass('color-p')) {
                $.post($self.data('cancelLikeUrl'), function(note) {
                    $self.find('.js-like-num').html(note.likeNum);
                }).always(function(){
                    $icon.removeClass('color-p');
                    $self .closest('.icon-favour').removeClass('active');
                });
            } else {
                $.post($self.data('likeUrl'), function(note) {
                    $self.find('.js-like-num').html(note.likeNum);
                }).always(function(){
                    $icon.addClass('color-p');
                    $self.closest('.icon-favour').addClass('active');
                });
                
            }
        });

    };

});