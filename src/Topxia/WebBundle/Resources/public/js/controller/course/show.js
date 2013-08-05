define(function(require, exports, module) {

    exports.run = function() {
        $('#teacher-carousel').carousel({interval: 0});
        $('#teacher-carousel').on('slide.bs.carousel', function (e) {
            var teacherId = $(e.relatedTarget).data('id');

            $('#teacher-detail').find('.teacher-item').removeClass('teacher-item-active');
            $('#teacher-detail').find('.teacher-item-' + teacherId).addClass('teacher-item-active');
        })

        var reviewTabInited = false;
        $("#course-review-tab").on('show.bs.tab', function(e) {
            if (reviewTabInited) {
                return ;
            }
            var $this = $(this),
                $pane = $($this.attr('href'));

            $.get($this.data('url'), function(html) {
                $pane.html(html);
                reviewTabInited =  true;
            });

            $pane.on('click', '.pagination a', function(e) {
                e.preventDefault();
                $.get($(this).attr('href'), function(html){
                    $pane.html(html);
                });
            });

        });

        $(".show-course-review-pane").click(function(){
            $("#course-review-tab").tab('show');
            var offset = $("#course-nav-tabs").offset();
            console.log(offset);
            $(document).scrollTop(offset.top - 20);
        }); 

        $("#favorite-btn").on('click', function() {
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $btn.hide();
                $("#unfavorite-btn").show();
            });
        });

        $("#unfavorite-btn").on('click', function() {
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $btn.hide();
                $("#favorite-btn").show();
            });
        });

    };

});