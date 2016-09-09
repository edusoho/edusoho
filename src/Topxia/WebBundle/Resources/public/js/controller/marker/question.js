define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var DraggableWidget = require('../marker/mange');
    require('jquery.intro');
    require('jquery.intro-css');
    var Cookie = require('cookie');

    exports.run = function() {
        $form = $('.mark-from');
        $.post($form.attr('action'), $form.serialize(), function(response) {
            $('#subject-lesson-list').html(response);
            $('[data-toggle="popover"]').popover();
            if(!Cookie.get("marker-manage-guide")){
                initIntro();
            } 
            Cookie.set("marker-manage-guide",'true',{expires:360,path:"/"});
        });
        var validator = new Validator({
            element: $form,
            autoSubmit: false,
            autoFocus: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return;
                }
                $.post($form.attr('action'), $form.serialize(), function(response) {
                    $('#subject-lesson-list').html(response);
                });
            }
        });
        var target = $('select[name=target]');
        $("#subject-lesson-list").on('click', '.pagination a', function(event) {
            event.preventDefault();
            console.log(target.val());
            $.post($(this).attr('href'), {
                "target": target.val()
            }, function(response) {
                $('#subject-lesson-list').html(response);
            })
        })

        $(".marker-manage").on('click', '.js-question-preview', function(e) {
            $.get($(this).data('url'), function(response) {
                $('.modal').modal('show');
                $('.modal').html(response);
            })
        })  

        var target = $('select[name=target]');
        $(".marker-manage").on('click', '.js-more-questions', function(e) {
            var $this = $(this).hide().parent().addClass('loading'),
                $list = $('#subject-lesson-list').css('max-height',$('#subject-lesson-list').height()),
                getpage = parseInt($this.data('current-page'))+1,
                lastpage =$this.data('last-page');
            $.post($this.data('url')+getpage, {
                "target": target.val()
            }, function(response) {
                $this.remove();
                $list.append(response).animate({scrollTop:40*($list.find('.item-lesson').length+1)});
                if(getpage == lastpage) {
                    $('.js-more-questions').parent().remove();
                }
            });

        })
        
        function initIntro() {
            var $list = $('#subject-lesson-list'),
                imgurl =$list.attr('data-intro-img'),
                imgheight = $(window).height()-$list.find('li:first-child').offset().top - 210,
                imgleft = (158*imgheight/286+70) > 0 ?(158*imgheight/286+70) : 0,
                introimg = "<div class='intro-img' style='left:-"+imgleft/2+"px'><img  src=" + imgurl + " style='height:" + imgheight + "px'>[视频编辑区域]</div>",
                intro = introJs();

            $('#step-1').addClass('color-warning');
            intro.setOptions({
                nextLabel: '知道了',
                doneLabel: '知道了',
                prevLabel: '上一步',
                hidePrev: true,
                showBullets: false,
                steps: [{
                    element: '#step-1',
                    intro: "为保险起见，所有对视频的编辑操作都将实时保存。",
                    position: 'bottom-middle-aligned',
                }, {
                    element: $list.find('li:first-child .icon-drag')[0],
                    intro: "<p class='title'>添加随堂练习，只需一步操作</p><div class='remask'>将题目拖拽到<span class='color-warning'>「视频编辑区域」</span></div>" + introimg,
                    position: 'left',
                }]
            }).start().onchange(function() {
                $('.introjs-skipbutton').css('display', 'inline-block');
                $('.introjs-prevbutton').css('display', 'none');
                $('#step-1').removeClass('color-warning');
            })
        }
    };

});
