define(function(require, exports, module) {
    
    require('jquery.perfect-scrollbar');

    $(".es-bar .bar-main-body").perfectScrollbar({wheelSpeed:50});

    if ($('.es-bar [data-toggle="tooltip"]').length > 0) {
        $('.es-bar [data-toggle="tooltip"]').tooltip({container: '.es-bar'});
    }

    // popover
    $(".es-bar .bar-menu-sns li.popover-btn").popover({
        placement: 'left',
        trigger: 'hover',
        html: true,
        content: function() {
            return $($(this).data('contentElement')).html();
        }
    });


    $(".es-bar").on('click','.js-bar-shrink',function() {
        var $this = $(this);
        $this.parents(".es-bar-main.active").removeClass('active').end().parents(".es-bar").animate({
            right: '-230px'
        },300);
        $(".bar-menu-top li.active").removeClass('active');
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


        if($("#notLogin").length>0){
            isNotLogin();
            return;
        }

        if($this.find(".dot")) {
          $this.find(".dot").remove();  
        }

        if(!$this.hasClass('active')) {
            $this.siblings(".active").removeClass('active').end().addClass('active').parents(".es-bar").animate({
                right: '0'
            },300);
            clickBar($this);
            $($this.data('id')).siblings(".es-bar-main.active").removeClass('active').end().addClass('active');
        }else {
            $this.removeClass('active').parents(".es-bar").animate({
                right: '-230px'
            },300);
        }

    });

    function isNotLogin(){
        $('.modal').modal('hide');

        $("#login-modal").modal('show');
        $.get($('#login-modal').data('url'), function(html){
            $("#login-modal").html(html);
        });
    }

    function clickBar($this){
        switch ($this.data('id')){
            case '#bar-course-list':
                var url = $("#bar-course-btn").data('url');
                
                if(!$("#bar-course-list").data('isLoad')){
                    $.get(url,function(html){
                        $("#bar-course-list").html(html);
                        $("#bar-course-list").data('isLoad', true);
                        $(".es-bar .bar-main-body").perfectScrollbar({wheelSpeed:50});
                    })
                }
                break;
            case '#bar-history':
                var url = $("#bar-my-history").data('url');
                $.get(url,function(html){
                    $("#bar-history-list").html(html);
                })
                break;
            case '#bar-message':
                var url = $("#bar-message-btn").data('url');
                $.get(url,function(html){
                    $(".bar-message").html(html);
                })
                break
            case '#bar-homework':
                var url = $("#bar-practice-review").data('url');
                if(!$("#bar-homework").data('isLoad')) {
                    $.get(url, function (html) {
                        $("#bar-homework").html(html);
                        $("#bar-homework").data('isLoad', true);
                        $(".es-bar .bar-main-body").perfectScrollbar({wheelSpeed:50});
                    })
                }
                break;
            default :
                break;
        }
    }

    var isIE10 = /MSIE\s+10.0/i.test(navigator.userAgent)
        && (function() {"use strict";return this === undefined;}());

    var isIE11 = (/Trident\/7\./).test(navigator.userAgent);

    if (isIE10 || isIE11) {
        $(".es-bar").css( "margin-right",'16px');
    }

    // 回到顶端
    var goTop = function() {
        var $gotop = $(".go-top");

        $(window).scroll(function(event) {
            var scrollTop = $(window).scrollTop();

            if(scrollTop>=300) {
                $gotop.addClass('show');

            }else if($gotop.hasClass('show')) {
                $gotop.removeClass('show');
            }
        });
        $gotop.click(function() {
            return $("body,html").animate({
                scrollTop: 0
            }, 300), !1
        });
    }();

    $("#bar-homework").on('click','#bar-practice-review',function(){
        var url = $("#bar-practice-review").data('url');
        $.get(url,function(html){
            $("#bar-homework").html(html);
            $(".es-bar .bar-main-body").perfectScrollbar({wheelSpeed:50});
        })
    });

    $("#bar-homework").on('click','#bar-practice-finish',function(){
        var url = $("#bar-practice-finish").data('url');
        $.get(url,function(html){
            $("#bar-homework").html(html);
            $(".es-bar .bar-main-body").perfectScrollbar({wheelSpeed:50});
        })
    });

    $("#bar-course-list").on('click','#bar-course-btn',function(){
        var url = $("#bar-course-btn").data('url');
        $.get(url,function(html){
            $("#bar-course-list").html(html);
            $(".es-bar .bar-main-body").perfectScrollbar({wheelSpeed:50});
        })
    });

    $("#bar-course-list").on('click','#bar-classroom-btn',function(){
        var url = $("#bar-classroom-btn").data('url');
        $.get(url,function(html){
            $("#bar-course-list").html(html);
            $(".es-bar .bar-main-body").perfectScrollbar({wheelSpeed:50});
        })
    });

});