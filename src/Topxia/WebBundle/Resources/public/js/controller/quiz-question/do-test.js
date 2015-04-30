define(function(require, exports, module) {

    require('placeholder');

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    require('ckeditor');

    var AudioPlayer = require('./audioplayer');
    var playedQuestions = [];
    var playingQuestion = null;

    var wrongs = [],

    rights = [],

    alls = [];

    var isAjaxing = 0;

    exports.run = function() {

        $('#testpaper-navbar').affix({
            offset: {
              top: 200
            }

        });

     $(".testpaper-card").find(".panel-body").css({"height": "auto","overflow": "hidden"});
  
      if ($('#testpaper-navbar').length > 0) {
            var $navbarTop=$('#testpaper-navbar').offset().top;
            $(window).scroll(function(){
         
            if ($('#testpaper-navbar').offset().top > $navbarTop) {
                var $cardFooterHeight= $(".testpaper-card").find('.panel-footer').css('height').replace('px','');
                var $cardHeadingHeight= $(".testpaper-card").find('.panel-heading').css('height').replace('px','');
                var $testpaperNavbarHeight= $(".testpaper-card").find('.panel-heading').css('height').replace('px','');
                    $(".testpaper-card").find(".panel-body").css({"max-height":$(window).height()-$cardFooterHeight-$cardHeadingHeight-$testpaperNavbarHeight-40,"height": "auto","overflow": "auto"});
                } else {
                    $(".testpaper-card").find(".panel-body").scrollTop(0);
                     $(".testpaper-card").find(".panel-body").css({"height": "auto","overflow": "hidden"});
                }
            });

            $('.testpaper-card').affix({
                offset: {
                  top: 200
                }
            });
      } else {
        var $testpaperHeadingHeight=$('.testpaper-heading').height();

        $(window).scroll(function(){
            if ($(window).scrollTop() > $testpaperHeadingHeight) {
                var $cardFooterHeight= $(".testpaper-card").find('.panel-footer').css('height').replace('px','');
                $(".testpaper-card").find(".panel-body").css({"max-height":$(window).height()-$cardFooterHeight-80,"height": "auto","overflow": "auto"});
            } else {
                $(".testpaper-card").find(".panel-body").scrollTop(0);
                 $(".testpaper-card").find(".panel-body").css({"height": "auto","overflow": "hidden"});
            }
        });

        $('.testpaper-card').affix({
            offset: {
              top: 400
            }
        });
      }


        $('.testpaper-card').on('click', '.btn-index', function() {
            var position = $($(this).data('anchor')).offset();
            $(document).scrollTop(position.top - 55);
        });


        var $body = $(document.body);

        $body.scrollspy({
            target: '#testpaper-navbar',
            offset: 120
        });

        $(window).on('load', function () {
            $body.scrollspy('refresh');
        });

// 做试卷
        var interval = 600;

        var changeAnswers = {};

        var deadline = $('#time_show').data('time');

        var usedTime = 0 ;

        var isLimit = true;

        var timerFinish = null;

        if (deadline == null) {
            isLimit = false;
            deadline = interval*3;
        } else {
            isLimit = true;
        }

        var timeLastPost = deadline - interval;

    //计时器...

        if ($('#time_show').hasClass('preview')) {
            $('#time_show').text(formatTime(deadline));
        }

        if ($('#finishPaper').hasClass('do-test')){

            var timer = timerShow(function(){
                deadline--;
                usedTime++;
                $('#time_show').text(formatTime(deadline));

                if (deadline < 0) {

                    timer.stop();

                    if (isAjaxing == 0) {
                        $.post($('#finishPaper').data('url'), {data:changeAnswers, usedTime:usedTime }, function(){
                            changeAnswers = {};
                            usedTime = 0;
                            $('#timeout-dialog').show();
                            timer.stop();
                        }).error(function(){
                            $('#timeout-dialog').find('.empty').text('系统好像出了点小问题，请稍后再交卷');
                            $('#timeout-dialog').find('#show_testpaper_result').text('确定');
                            $('#timeout-dialog').show();
                            timer.stop();
                        });
                    } else {
                        timerFinish = setInterval(function(){
                            if (isAjaxing == 0) {
                                clearInterval(timerFinish);
                                $.post($('#finishPaper').data('url'), {data:changeAnswers, usedTime:usedTime }, function(){
                                    changeAnswers = {};
                                    usedTime = 0;
                                    $('#timeout-dialog').show();
                                }).error(function(){
                                    $('#timeout-dialog').find('.empty').text('系统好像出了点小问题，请稍后再交卷');
                                    $('#timeout-dialog').find('#show_testpaper_result').text('确定');
                                    $('#timeout-dialog').show();
                                });
                            }
                        }, 1000);
                    }

                }
                if (deadline == timeLastPost) {

                    isAjaxing = 1;

                    timeLastPost = timeLastPost - interval;
                    $.post($('#finishPaper').data('ajax'), { data:changeAnswers, usedTime:usedTime }, function(){
                        changeAnswers = {};
                        isAjaxing = 0;
                        usedTime = 0;
                    });

                    if (!isLimit){
                        deadline = interval*3;
                        timeLastPost = deadline - interval;
                    }
                }
            }, 1000, true);

            $('#pause').on('click', function(){
                timer.pause();
            });

            $('div#modal').on('hidden.bs.modal',function(){
                timer.play();
            });

        }


    //...

        $('*[data-type]').each(function(index){
            var name = $(this).attr('name');

            $(this).on('change', function(){
                // var name = $(this).attr('name');

                var values = [];
                //choice
                if ($(this).data('type') == 'choice') {

                    $('input[name='+name+']:checked').each(function(){
                        values.push($(this).val());
                    });

                }
                //determine
                if ($(this).data('type') == 'determine') {

                    $('input[name='+name+']:checked').each(function(){
                        values.push($(this).val());
                    });

                }
                //fill
                if ($(this).data('type') == 'fill') {

                    $('input[name='+name+']').each(function(){
                        values.push($(this).val());
                    });

                }
                //essay
                if ($(this).data('type') == 'essay') {
                    var $this = $(this);
                    if ($this.val() != "") {
                        values.push($this.val());
                    }
                }

                changeAnswers[name] = values;


                if (values.length > 0 && !isEmpty(values)) {
                    $('a[data-anchor="#question' + name + '"]').addClass('active');
                } else {
                    $('a[data-anchor="#question' + name + '"]').removeClass('active');
                }


            });

        });

        $('.testpaper-question-actions').on('click', 'a.marking', function(){

            id = $(this).parents('.testpaper-question').attr('id');
            btn = $('.testpaper-card .panel-body [data-anchor="#'+id+'"]');

            btn.addClass('have-pro');

            $(this).hide();
            $(this).parent().find('a.btn.unMarking').show();
            
        });

        $('.testpaper-question-actions').on('click', 'a.unMarking', function(){
            id = $(this).parents('.testpaper-question').attr('id');
            btn = $('.testpaper-card .panel-body [data-anchor="#'+id+'"]');

            btn.removeClass('have-pro');

            $(this).hide();
            $(this).parent().find('a.btn.marking').show();

        });

        $('body').on('click', '#finishPaper', function(){
            $('#testpaper-finished-dialog').modal('show');
        });

        $('#testpaper-finish-btn').on('click', function(){
            $finishBtn = $('#finishPaper');
            $('#testpaper-finish-btn').button('saving');
            $('#testpaper-finish-btn').attr('disabled', 'disabled');

            timer.stop();

            if (isAjaxing == 0) {
                $.post($finishBtn.data('url'), { data:changeAnswers, usedTime:usedTime }, function(){
                    window.location.href = $finishBtn.data('goto');
                });
            } else {
                timerFinish = setInterval(function(){
                    if (isAjaxing == 0) {
                        clearInterval(timerFinish);
                        $.post($finishBtn.data('url'), { data:changeAnswers, usedTime:usedTime }, function(){
                            window.location.href = $finishBtn.data('goto');
                        });
                    }
                }, 1000);
            }
        });

        $('body').on('click', '#suspend', function(){
            $suspendBtn = $(this);

            $.post($(this).data('url'), { data:changeAnswers, usedTime:usedTime }, function(){
                window.location.href = $suspendBtn.data('goto');
            });

        });

        var isDoing = $('.testpaper-status-doing').length > 0;

        $('.testpaper-question-choice').on('click', 'ul.testpaper-question-choices li', function(){
            $input = $(this).parents('div.testpaper-question-choice').find('.testpaper-question-choice-inputs label').eq($(this).index()).find('input');
            isChecked = $input.prop("checked");

            $input.prop("checked", !isChecked).change();

            $input.parents('.testpaper-question-choice-inputs').find('label').each(function(){
                $(this).find('input').prop("checked") ? $(this).addClass('active') : $(this).removeClass('active');
            });

            if (isDoing) {
                var $question = $(this).parents('.testpaper-question').next();
                playQuestion($question);
            }
            
        });

        if (isDoing) {
            setTimeout(function(){
                playQuestion($('.testpaper-body').find('.testpaper-question:first'));
            }, 2000);
        }

        $('.testpaper-question-choice-inputs,.testpaper-question-determine-inputs').on('click', 'input', function(){
            $input = $(this);
            $input.parents('.testpaper-question-choice-inputs,.testpaper-question-determine-inputs').find('label').each(function(){
                $(this).find('input').prop("checked") ? $(this).addClass('active') : $(this).removeClass('active');
            });

            if (isDoing) {
                var $question = $(this).parents('.testpaper-question').next();
                playQuestion($question);
            }

        });


        $('body').on('click', '.favorite-btn', function(){
            $btn = $(this);
            $.post($(this).data('url'),function(){
                $btn.hide();
                $btn.parent().find('.unfavorite-btn').show();
            });
        });

        $('body').on('click', '.unfavorite-btn', function(){
            $btn = $(this);
            $.post($(this).data('url'),function(){
                $btn.hide();
                $btn.parent().find('.favorite-btn').show();
            });
        });

        
// 学生查看试卷结果

        $('.testpaper-card .panel-body a.btn[data-anchor^="#question"]').each(function(){

            if ($(this).hasClass('wrong')) {
                wrongs.push($(this).attr('data-anchor'));
                $(this).addClass('btn-danger');
            }
            if ($(this).hasClass('right')) {
                rights.push($(this).attr('data-anchor'));
                $(this).addClass('btn-success');
            }
            if ($(this).hasClass('checking')) {
                
                $(this).addClass('btn-warning');
            }
            alls.push($(this).attr('data-anchor'));
        });

        $('.testpaper-card').on('click', '#showWrong', function(){
            $.each(alls, function(index, val){
                if ($.inArray(val, wrongs) < 0) {
                    $(val).toggle();
                }
            });

            $('.testpaper-question-block').each(function(){
                var isHidden = true;
                $(this).find('div.testpaper-question').each(function(){
                    id = $(this).attr('id');   

                    if ($.inArray('#'+id, wrongs) >= 0) {
                        isHidden = false;
                    }
                });

                if (isHidden){
                    $(this).toggle();
                }
            });

            var testpaperBodyHeight = $('.testpaper-body').height();
            var testpaperCardHeight = $('.testpaper-card').height();
            if (testpaperCardHeight > testpaperBodyHeight) {
                $('.testpaper-body').css('min-height', testpaperCardHeight);
            }
        });

        $.each(alls, function(index, val){
            $(val).on('click', '.testpaper-question-actions a.analysis-btn', function(){
                $(this).parents('.testpaper-question').find('div.well').show();
                $(this).parent().find('.unanalysis-btn').show();
                $(this).hide()
            });

            $(val).on('click', '.testpaper-question-actions a.unanalysis-btn', function(){
                $(this).parents('.testpaper-question').find('div.well').hide();
                $(this).parent().find('.analysis-btn').show();
                $(this).hide();
            });
        });


        //问答题富文本编辑器部分



        $('.testpaper-question-essay-input-short').focusin(function(e) {

            e.preventDefault();
            e.stopPropagation();
            $(this).blur();

            var $shortTextarea = $(this).hide();
            var $longTextarea = $shortTextarea.parent().find('.testpaper-question-essay-input-long').show();
            var $textareaBtn = $shortTextarea.parent().find('.testpaper-question-essay-input-btn').show();

            var editor = CKEDITOR.replace($longTextarea.attr('id'), {
                toolbar: 'Simple',
                filebrowserImageUploadUrl: $longTextarea.data('imageUploadUrl')
            });

            editor.on('blur', function(e) {
                editor.updateElement();
                setTimeout(function() {
                    $longTextarea.val(editor.getData());
                    $longTextarea.change();
                }, 1);
            });

            editor.on('instanceReady', function(e) {
                this.focus();

                $textareaBtn.one('click', function() {
                    $shortTextarea.val($(editor.getData()).text());
                    editor.destroy();
                    $longTextarea.hide();
                    $textareaBtn.hide();
                    $shortTextarea.show();
                });
            });

            editor.on('key', function(){
                editor.updateElement();
                setTimeout(function() {
                    $longTextarea.val(editor.getData());
                    $longTextarea.change();
                }, 1);
            });

            editor.on('insertHtml', function(e) {
                editor.updateElement();
                setTimeout(function() {
                    $longTextarea.val(editor.getData());
                    $longTextarea.change();
                }, 1);
            });

        });


        $('.testpaper-question-essay-teacherSay-short').focusin(function(e) {

            e.preventDefault();
            e.stopPropagation();
            $(this).blur();

            var $shortTextarea = $(this).hide();
            var $longTextarea = $shortTextarea.parent().find('.testpaper-question-essay-teacherSay-long').show();
            var $textareaBtn = $shortTextarea.parent().find('.testpaper-question-essay-teacherSay-btn').show();


            var editor = CKEDITOR.replace($longTextarea.attr('id'), {
                toolbar: 'Simple',
                filebrowserImageUploadUrl: $longTextarea.data('imageUploadUrl')
            });

            editor.on('blur', function(e) {
                editor.updateElement();
            });

            editor.on('instanceReady', function(e){
                this.focus();

                $textareaBtn.one('click', function(){
                    $shortTextarea.val($(editor.getData()).text());
                    editor.destroy();
                    $longTextarea.hide();
                    $textareaBtn.hide();
                    $shortTextarea.show();
                });
            });

            editor.on('key', function(){
                editor.updateElement();
                $longTextarea.change();
            });

        });

        //老师阅卷校验

        $('#testpaper-checked-dialog').modal({
            'show': false
        });
        
        if ($('#teacherCheckForm').length > 0) {
            var validator = new Validator({
                element: '#teacherCheckForm',
                autoSubmit: false
            });

            Validator.addRule('score', function(options) {
                var element = options.element;
                var isFloat = /^\d+(\.\d)?$/.test(element.val());
                if (!isFloat){
                    return false;
                }

                if (Number(element.val()) <= Number(element.data('score'))) {
                    return true;
                } else {
                    return false;
                }
            }, '{{display}}只能是<=题目分数、且>=0的整数或者1位小数');
        }

        $('[name^="score_"]').each(function(){
            name = $(this).attr('name');
            validator.addItem({
                element: '[name='+name+']',
                required: true,
                rule: 'score',
                display: '得分'
            });
        });

        $('#finishCheck').on('click', function(){
            validator.execute(function(error, results, element) {
                if (error) {
                    return false;
                }
                $('#testpaper-checked-dialog').modal('show');
            });
        });

        $('#testpaper-teacherSay-select').change(function() {
            var $option = $(this).find('option:checked');
            if ($option.val() == '') {
                $('#testpaper-teacherSay-input').val('');
            } else {
                $('#testpaper-teacherSay-input').val($option.text());
            }
        });

        $('#testpaper-teacherSay-btn').on('click', function(){
            val = $('#testpaper-teacherSay-input').val();
            $('#teacherSay').val(val);

            $form = $('#teacherCheckForm');

            $.post($('#testpaper-teacherSay-btn').data('post-url'), $form.serialize(), function(response) {
                window.location.href = $('#testpaper-teacherSay-btn').data('goto');
            })
        });

    };


    function isEmpty(values) {
        for (key in values) {
            if (values[key] != '') {
                return false;
            }
        }
        return true;
    }



    function timerShow(func, time, autostart) {
        this.set = function(func, time, autostart) {
            this.init = true;
            if(typeof func == 'object') {
                var paramList = ['autostart', 'time'];
                for(var arg in paramList) {
                    if(func[paramList[arg]] != undefined) {
                        eval(paramList[arg] + " = func[paramList[arg]]");
                    }
                };
                func = func.action;
            }
            if(typeof func == 'function') {this.action = func;}
            if(!isNaN(time)) {this.intervalTime = time;}
            if(autostart && !this.isActive) {
                this.isActive = true;
                this.setTimer();
            }
            return this;
        };
        this.once = function(time) {
            var timer = this;
            if(isNaN(time)) {
                time = 0;
            }
            window.setTimeout(function() {timer.action();}, time);
            return this;
        };
        this.play = function(reset) {
            if(!this.isActive) {
                if(reset) {this.setTimer();}
                else {this.setTimer(this.remaining);}
                this.isActive = true;
            }
            return this;
        };
        this.pause = function() {
            if(this.isActive) {
                this.isActive = false;
                this.remaining -= new Date() - this.last;
                this.clearTimer();
            }
            return this;
        };
        this.stop = function() {
            this.isActive = false;
            this.remaining = this.intervalTime;
            this.clearTimer();
            return this;
        };
        this.toggle = function(reset) {
            if(this.isActive) {this.pause();}
            else if(reset) {this.play(true);}
            else {this.play();}
            return this;
        };
        this.reset = function() {
            this.isActive = false;
            this.play(true);
            return this;
        };
        this.clearTimer = function() {
            window.clearTimeout(this.timeoutObject);
        };
        this.setTimer = function(time) {
            var timer = this;
            if(typeof this.action != 'function') {return;}
            if(isNaN(time)) {time = this.intervalTime;}
            this.remaining = time;
            this.last = new Date();
            this.clearTimer();
            this.timeoutObject = window.setTimeout(function() {timer.go();}, time);
        };
        this.go = function() {
            if(this.isActive) {
                this.action();
                this.setTimer();
            }
        };

        if(this.init) {
            return new $.timer(func, time, autostart);
        } else {
            this.set(func, time, autostart);
            return this;
        }
    };

    function formatTime(time) {
        // time = time / 10;
        var min = parseInt(time / 60),
        sec = time - (min * 60);
        return (min > 0 ? pad(min, 2) : "00") + ":" + pad(sec, 2);
    };
    function pad(number, length) {
        var str = '' + number;
        while (str.length < length) {str = '0' + str;}
        return str;
    };


    function playQuestion($question)
    {
        if ($question.length == 0) {
            return ;
        }

        if ($.inArray($question.attr('id'), playedQuestions) != -1) {
            return ;
        }

        playedQuestions.push($question.attr('id'));

        var $playTriggers = $question.find('.audio-play-trigger');
        if ($playTriggers.length > 0) {
            $.each($playTriggers, function(i, trigger) {
                $(document).queue('audio_player', function() {
                    playingQuestion = true;
                    var player = new AudioPlayer({
                        element: trigger
                    });

                    player.on('ended', function() {
                        playingQuestion = false;
                        $(document).dequeue('audio_player');
                    });

                });
            });

            if (!playingQuestion) {
                $(document).dequeue('audio_player');
            }
        }
    }


});

