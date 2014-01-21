define(function(require, exports, module) {

    var EditorFactory = require('common/kindeditor-factory');

    var wrongs = [],

    rights = [],

    alls = [];

    exports.run = function() {

        var $body = $(document.body);

        $body.scrollspy({
            target: '#testpaper-navbar',
            offset: 120
        });

        $(window).on('load', function () {
            $body.scrollspy('refresh');
        });








// 做试卷
        var interval = 180;

        var changeAnswers = {};

        var deadline = $('#time_show').data('time');

        var timeLastPost = deadline - interval;

    //计时器...

        if ($('#time_show').hasClass('preview')) {
            $('#time_show').text(formatTime(deadline));
            deadline = undefined;
        }

        if(deadline != undefined) {
            var timer = timerShow(function(){
                deadline--;
                $('#time_show').text(formatTime(deadline));

                if (deadline <= 0) {
                    $.post($('#finishPaper').data('url'), {data:changeAnswers, remainTime:deadline }, function(){
                        changeAnswers = {};
                        window.location.href = $('#finishPaper').data('goto');
                    });
                }
                if (deadline == timeLastPost) {
                    timeLastPost = timeLastPost - interval;
                    $.post($('#finishPaper').data('ajax'), { data:changeAnswers, remainTime:deadline }, function(){
                        changeAnswers = {};
                    });
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
                    if ($(this).val() != "") {
                        values.push($(this).val());
                    }     
                }

                changeAnswers[name] = values;


                if (values.length > 0 && !isEmpty(values)) {
                    $('a[href="#question' + name + '"]').addClass('active');
                } else {
                    $('a[href="#question' + name + '"]').removeClass('active');
                }


            });

        });

        $('.testpaper-question-actions').on('click', 'a.marking', function(){

            id = $(this).parents('.testpaper-question').attr('id');
            btn = $('.testpaper-card .panel-body [href="#'+id+'"]');

            btn.addClass('have-pro');

            $(this).hide();
            $(this).parent().find('a.btn.unMarking').show();
            
        });

        $('.testpaper-question-actions').on('click', 'a.unMarking', function(){
            id = $(this).parents('.testpaper-question').attr('id');
            btn = $('.testpaper-card .panel-body [href="#'+id+'"]');

            btn.removeClass('have-pro');

            $(this).hide();
            $(this).parent().find('a.btn.marking').show();

        });

        $('body').on('click', '#finishPaper', function(){
            $finishBtn = $(this);

            $.post($(this).data('url'), { data:changeAnswers, remainTime:deadline }, function(){
                window.location.href = $finishBtn.data('goto');
            });

        });

        $('body').on('click', '#suspend', function(){
            $suspendBtn = $(this);

            $.post($(this).data('url'), { data:changeAnswers, remainTime:deadline }, function(){
                window.location.href = $suspendBtn.data('goto');
            });

        });

        $('.testpaper-question-choice').on('click', 'ul.testpaper-question-choices li', function(){
            $input = $(this).parents('div.testpaper-question-choice').find('.testpaper-question-choice-inputs label').eq($(this).index()).find('input');
            isChecked = $input.prop("checked");

            $input.prop("checked", !isChecked).change();

            $input.parents('.testpaper-question-choice-inputs').find('label').each(function(){
 
                $(this).find('input').prop("checked") ? $(this).addClass('active') : $(this).removeClass('active');
            });
            
        });

        $('.testpaper-question-choice-inputs,.testpaper-question-determine-inputs').on('click', 'input', function(){
            $input = $(this);
            $input.parents('.testpaper-question-choice-inputs,.testpaper-question-determine-inputs').find('label').each(function(){

                $(this).find('input').prop("checked") ? $(this).addClass('active') : $(this).removeClass('active');
            });
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

        $('.testpaper-card .panel-body a.btn[href^="#question"]').each(function(){

            if ($(this).hasClass('wrong')) {
                wrongs.push($(this).attr('href'));
                $(this).addClass('btn-danger');
            }
            if ($(this).hasClass('right')) {
                rights.push($(this).attr('href'));
                $(this).addClass('btn-success');
            }
            alls.push($(this).attr('href'));
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

        //老师批阅

        // $('.testpaper-card').on('click', '#postPaper', function(){
        //     $finishBtn = $(this);

        //     $.post($(this).data('url'), $('#teacherCheckForm').serialize(), function(){
        //         window.location.href = $finishBtn.data('goto');
        //     });

        // });

        $('#teacherCheckForm').submit(function(){

            $.post($('#finishCheck').data('post-url'), $('#teacherCheckForm').serialize(), function(){
                window.location.href = $('#finishCheck').data('goto');
            });
            return false;
        });


        //问答题富文本编辑器部分



        $('.testpaper-question-essay-input-btn').focus(function(){

            var editor = EditorFactory.create($(this), 'simple', {

                extraFileUploadParams:{group:'default'},

                textarea: $(this),

                afterBlur: function(){
                    editor.sync();
                    this.textarea.show();
                    editor.remove();
                },

                afterChange: function(){
                    this.sync();
                    this.textarea.change();
                }
            });

            $(this).hide();
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


});

