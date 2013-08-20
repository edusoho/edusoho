define(function(require, exports, module) {

    exports.run = function() {
        $('#teacher-carousel').carousel({interval: 0});
        $('#teacher-carousel').on('slide.bs.carousel', function (e) {
            var teacherId = $(e.relatedTarget).data('id');

            $('#teacher-detail').find('.teacher-item').removeClass('teacher-item-active');
            $('#teacher-detail').find('.teacher-item-' + teacherId).addClass('teacher-item-active');
        })


        var Scroll = function(div, opt, callback){
                //参数初始化
                var $div =$(div);
                if(!opt) var opt={};
                var _this=$div.eq(0).find("ul:first");
                var        lineH=_this.find("li:first").height(), //获取行高
                        line=opt.line?parseInt(opt.line,10):parseInt($div.height()/lineH,10), //每次滚动的行数，默认为一屏，即父容器高度
                        speed=opt.speed?parseInt(opt.speed,10):500, //卷动速度，数值越大，速度越慢（毫秒）
                        timer=opt.timer?parseInt(opt.timer,10):3000; //滚动的时间间隔（毫秒）
                if(line==0) line=1;
                var upHeight=0-line*lineH;
                //滚动函数
                scrollUp=function(){
                        _this.animate({
                                marginTop:upHeight
                        },speed,function(){
                                for(i=1;i<=line;i++){
                                        _this.find("li:first").appendTo(_this);
                                }
                                _this.css({marginTop:0});
                        });
                }
                //鼠标事件绑定
                _this.hover(function(){
                        clearInterval(timerID);
                },function(){
                        timerID=setInterval("scrollUp()",timer);
                }).mouseout();
        };

        Scroll("#scroll-threads",{line:1,speed:500,timer:3000});
        Scroll("#scroll-announcements",{line:1,speed:500,timer:3000});

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