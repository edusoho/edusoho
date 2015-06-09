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
               if($(this).siblings(".content").height() >= 90) {
                   $(this).css("display","inline-block");
               }
           });

           noteListPShow.click(function(){
               var btn = $(this);
               if(btn.data('toggle') && btn.siblings(".content").height()) {
                   btn.siblings(".content").addClass("active");
                   btn.addClass('more-hidden').removeClass('more-show').text("[收起全文]");
                   btn.data('toggle', false);

               } else {
                   btn.siblings(".content").removeClass("active");
                   btn.addClass('more-show').removeClass('more-hidden').text("[展开全文]");
                   btn.data('toggle', true);
               }

           });
        }

        $ul.on('click', '.js-like', function() {
            var $self = $(this);
            if ($self.hasClass('color-primary')) {
                $.post($self.data('cancelLikeUrl'), function(note) {
                    $self.find('.js-like-num').html(note.likeNum);
                }).always(function(){
                    $self.removeClass('color-primary');
                    $self.closest('.icon-favour').removeClass('active');
                });
            } else {
                $.post($self.data('likeUrl'), function(note) {
                    $self.find('.js-like-num').html(note.likeNum);
                }).always(function(){
                    $self.addClass('color-primary');
                    $self.closest('.icon-favour').addClass('active');
                });
                
            }
        });

    };

});