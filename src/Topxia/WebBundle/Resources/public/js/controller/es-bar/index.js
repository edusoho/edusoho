define(function(require, exports, module) {
    
    require('jquery.perfect-scrollbar');

    var Widget = require('widget');

    var EsBar = Widget.extend({
        events: {
            'click .js-bar-shrink': 'onBarBhrink',
            'click .bar-menu-top li': 'onMenuTop',
            'click .btn-action >a':  'onBtnAction'
        },
        setup: function() {
            var isIE10 = /MSIE\s+10.0/i.test(navigator.userAgent)
                && (function() {"use strict";return this === undefined;}());
            var isIE11 = (/Trident\/7\./).test(navigator.userAgent);
            var isEdge = /Edge\/13./i.test(navigator.userAgent);
            if (isIE10 || isIE11 || isEdge) {
                $(".es-bar").css( "margin-right",'16px');
            }

            if ( this.element.find('[data-toggle="tooltip"]').length > 0) {
                this.element.find('[data-toggle="tooltip"]').tooltip({container: '.es-bar'});
            }

            this.element.find(".bar-menu-sns li.popover-btn").popover({
                placement: 'left',
                trigger: 'hover',
                html: true,
                content: function() {
                    return $($(this).data('contentElement')).html();
                }
            });

            $("body").on('click', '.es-wrap', function() {
                if ($(".es-bar-main.active").length) {
                    $(".es-bar").animate({
                        right: '-230px'
                    },300).find(".bar-menu-top li.active").removeClass('active');
                }
            });

            this._goTop();
        },
        onBarBhrink: function(e) {
            var $this = $(e.currentTarget);
            $this.parents(".es-bar-main.active").removeClass('active').end().parents(".es-bar").animate({
                right: '-230px'
            },300);
            $(".bar-menu-top li.active").removeClass('active');
        },
        onMenuTop: function(e) {
            var $this = $(e.currentTarget);

            // 判断是否登录
            if($("meta[name='is-login']").attr("content")==0){
                this.isNotLogin();
                return;
            }

            this.element.find(".bar-main-body").perfectScrollbar({wheelSpeed:50});

            if($this.find(".dot")) {
              $this.find(".dot").remove();  
            }

            if(!$this.hasClass('active')) {
                $this.siblings(".active").removeClass('active').end().addClass('active').parents(".es-bar").animate({
                    right: '0'
                },300);
                this.clickBar($this);
                $($this.data('id')).siblings(".es-bar-main.active").removeClass('active').end().addClass('active');
            }else {
                $this.removeClass('active').parents(".es-bar").animate({
                    right: '-230px'
                },300);
            }
        },
        onBtnAction: function(e) {
            var $this = $(e.currentTarget);
            var url = $this.data('url');

            $.get(url,function(html){
                $this.closest('.es-bar-main').html(html);
                $(".es-bar .bar-main-body").perfectScrollbar({wheelSpeed:50});
            })
        },
        isNotLogin: function() {
            var $loginModal = $("#login-modal");

            $loginModal.modal('show');
            $.get($loginModal.data('url'), function(html){
                $loginModal.html(html);
            });
        },
        clickBar: function($this) {
            if(typeof($this.find('a').data('url')) != 'undefined' ) {
                var url = $this.find('a').data('url');

                $.get(url,function(html){
                    $($this.data('id')).html(html);
                    $(".es-bar .bar-main-body").perfectScrollbar({wheelSpeed:50});
                })
            }
        },
        _goTop: function() {
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
        }
        
    });

    var esBar = new EsBar({
        element: '.es-bar'
    }).render();

});