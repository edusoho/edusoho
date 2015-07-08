define(function(require, exports, module) {
    
    require('jquery.perfect-scrollbar');

    $(".es-bar .bar-main-body").perfectScrollbar({wheelSpeed:50});

    // popover
    $(".es-bar .bar-menu-sns li.popover-btn").popover({
        placement: 'left',
        trigger: 'hover',
        html: true,
        content: function() {
            return $($(this).data('contentElement')).html();
        }
    });


    $(".js-bar-shrink").click(function() {
        var $this = $(this);
        $this.parents(".es-bar-main.active").removeClass('active').end().parents(".es-bar").animate({
            right: '-230px'
        },300);
    });
    
    $("body").on('click', '.es-wrap', function() {
        if ($(".es-bar-main.active").length) {
            $(".es-bar").animate({
                right: '-230px'
            },300).find(".bar-menu-top li.active").removeClass('active');
        }
    });


    $(".es-bar .bar-menu-top li").click(function(){
        var $this = $(this);
        if(!$this.hasClass('active')) {
            $this.siblings(".active").removeClass('active').end().addClass('active').parents(".es-bar").animate({
                right: '0'
            },300);
            if($this.data('id') == '#bar-course-list'){
                var url = $("#bar-course-btn").data('url');
                $.get(url,function(html){
                    $("#bar-my-list").html(html);
                })
            }
            $($this.data('id')).siblings(".es-bar-main.active").removeClass('active').end().addClass('active');
        }else {
            $this.removeClass('active').parents(".es-bar").animate({
                right: '-230px'
            },300);
        }
        
    });


    // 回到顶端
    var goTop = function() {
        $(".go-top").click(function() {
            return $("body,html").animate({
                scrollTop: 0
            }, 300), !1
        });
    }();

    $("#bar-course-btn").on('click',function(){
        var url = $("#bar-course-btn").data('url');
        $.get(url,function(html){
            $("#bar-my-list").html(html);
        })
    });

    $("#bar-classroom-btn").on('click',function(){
        var url = $("#bar-classroom-btn").data('url');
        $.get(url,function(html){
            $("#bar-my-list").html(html);
        })
    });

});