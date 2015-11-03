define(function(require, exports, module) {
    
    require('jquery.perfect-scrollbar');

    

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

        // 判断是否登录
        if($("meta[name='is-login']").attr("content")==0){
            isNotLogin();
            return;
        }

        $(".es-bar .bar-main-body").perfectScrollbar({wheelSpeed:50});

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
        var $loginModal = $("#login-modal");

        $loginModal.modal('show');
        $.get($loginModal.data('url'), function(html){
            $loginModal.html(html);
        });
    }

    function clickBar($this){
        if(typeof($this.find('a').data('url')) != 'undefined' ) {
            var url = $this.find('a').data('url');

            $.get(url,function(html){
                $($this.data('id')).html(html);
                $(".es-bar .bar-main-body").perfectScrollbar({wheelSpeed:50});
            })
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

    $(".es-bar").on('click','.btn-action >a',function(){
        var $this = $(this);
        var url = $this.data('url');

        $.get(url,function(html){
            $this.closest('.es-bar-main').html(html);
            $this.closest('.bar-main-body').perfectScrollbar({wheelSpeed:50});
        })
    });

});