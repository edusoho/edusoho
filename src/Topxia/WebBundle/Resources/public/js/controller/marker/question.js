define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    var Cookie = require('cookie');

    exports.run = function() {
        $form = $('.js-mark-from');
        $.post($form.attr('action'), $form.serialize(), function(response) {
            $('#subject-lesson-list').html(response);
            $('[data-toggle="popover"]').popover();
            if(!Cookie.get("marker-manage-guide")){
                initIntro();
            }else {
                require('../marker/mange');
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


        $(".js-marker-manage-content").on('click', '.js-question-preview', function(e) {
            $.get($(this).data('url'), function(response) {
                $('.modal').modal('show');
                $('.modal').html(response);
            })
        })

        var target = $('select[name=target]');
        $(".js-marker-manage-content").on('click', '.js-more-questions', function(e) {
            var $this = $(this).hide().parent().addClass('loading'),
                $list = $('#subject-lesson-list').css('max-height', $('#subject-lesson-list').height()),
                getpage = parseInt($this.data('current-page')) + 1,
                lastpage = $this.data('last-page');
            $.post($this.data('url') + getpage, {
                "target": target.val()
            }, function(response) {
                $this.remove();
                $list.append(response).animate({ scrollTop: 40 * ($list.find('.item-lesson').length + 1) });
                if (getpage == lastpage) {
                    $('.js-more-questions').parent().remove();
                }
            });

        })

        $(".js-marker-manage-content").on('click', '.js-close-introhelp', function(e) {
            var $this = $(this);
            $this.closest('.show-introhelp').removeClass('show-introhelp');
            if ($('.show-introhelp').height() <= 0) {
                $('.js-introhelp-overlay').addClass('hidden');
                require('../marker/mange');
            }
        });

        function initIntro() {
            $('.js-introhelp-overlay').removeClass('hidden');
            $('.show-introhelp').addClass('show');
            var $img = $('.js-introhelp-img img'),
                imgheight = $(window).height() - $img.offset().top - 80;
            left = imgheight * 158 / 286 / 2;
            $img.height(imgheight);
            $('.js-introhelp-img').css('margin-left', '-' + left + 'px');

        }
    };

});
