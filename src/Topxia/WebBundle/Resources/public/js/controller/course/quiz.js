define(function(require, exports, module) {

    exports.run = function() {

        var quizItemCount = 0;
        var Notify = require('common/bootstrap-notify');
        
        $(".quiz-page").on('click', ".start-quiz", function() {
            $(".quiz-content").show();
            $(".alreay-lesson-quiz").hide();
        });

        if ($(".quiz-page").find(".quiz-form").length == 1) {
            $('.next-item').removeClass('next-item').addClass("check-result").text("查看本次测验结果");
        };

        $(".quiz-page").find(".quiz-form").each(function(index) {
            if (index == 0) {
                $(this).attr({
                    name: "currentItem"
                });
                $(this).find("button[type='submit']").attr({
                    name: "currentSubmit"
                });
                $(this).find("input[name='user-choices']").attr({
                    name: "currentChoice"
                });
                $(this).show();
            }
        });

        $(".quiz-page").on('click', "button[type='submit'][name='currentSubmit']", function() {
            var answers = "";
            if ($("form[name='currentItem']").find("#item-type").text() == "multiple"){                
                $(this).parent().find(".choice").each(function(index) {

                    if ($(this).find("input[type='checkbox']").is(":checked") == true) {
                        answers += $(this).find("input[type='checkbox']").attr("name") + ";";
                        $(".quiz-page").find("input[name='currentChoice']").attr({
                            value: answers
                        });
                    }

                });
            }

            if ($("form[name='currentItem']").find("#item-type").text() == "single"){
                answers += $("form[name='currentItem']").find("input[name='single']:checked").val() + ";";
                $(".quiz-page").find("input[name='currentChoice']").attr({
                    value: answers
                });
            }
            
            if (answers.length == 0 || answers == "undefined;") {
                Notify.warning('你尚未提供答案, 请在提交之前选择您所认为正确的答案！');
                return false;
            }

            $(".next-item").show();

            if (quizItemCount == $(".quiz-form").length - 1 ) {
                $('.next-item').removeClass('next-item').addClass("check-result").text("查看本次测验结果");
            }

            if($(".quiz-form").length == 1){
                $('.modal-footer').find(".check-result").show();
            }

            $.post($(this).parent().attr('action'), $(this).parent().serialize(), function(response) {
                var arrayAnswers = response.answers.split(";");
                if ($("form[name='currentItem']").find("#item-type").text() == "multiple"){
                    $("form[name='currentItem']").find(".choice").each(function(index) {
                        $(this).find("input[type='checkbox']").attr("disabled", true);
                        var result = $.inArray($(this).find("input[type='checkbox']").attr("name"), arrayAnswers);
                        if (result == -1) {
                            if ($(this).find("input[type='checkbox']").is(":checked") == true) {
                                $(this).append("<span style='text-align:center;width:200px; font-size: 14pt; color:red'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 错误选项，你选择了，非常的可惜! </span>");
                            }
                        } else {
                            if ($(this).find("input[type='checkbox']").is(":checked") == true) {
                                $(this).append("<span style='text-align:center;width:200px; font-size: 14pt; color:green'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 正确选项，你选择了，明智的决定! </span>");
                            } else {
                                $(this).append("<span style='text-align:center;width:200px; font-size: 14pt; color:red'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 正确选项，你漏选了，非常的可惜! </span>");
                            }
                        }
                    });
                } else if($("form[name='currentItem']").find("#item-type").text() == "single"){
                    $("form[name='currentItem']").find(".choice").each(function(index) {
                        $(this).find("input[type='radio']").attr('disabled', true);
                        var result = $.inArray($("form[name='currentItem']").find("input[name='single']:checked").val(), arrayAnswers);
                        if (result == -1) {
                            if ($(this).find("input[type='radio']").is(":checked") == true) {
                                $(this).append("<span style='text-align:center;width:200px; font-size: 14pt; color:red'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 错误选项，你选择了，非常的可惜! </span>");
                            }
                            if($(this).find("input[type='radio']").val() == arrayAnswers[0]){
                                $(this).append("<span style='text-align:center;width:200px; font-size: 14pt; color:red'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 正确选项，你漏选了，非常的可惜！ </span>");
                            }

                        } else {
                            if ($(this).find("input[type='radio']").is(":checked") == true) {
                                $(this).append("<span style='text-align:center;width:200px; font-size: 14pt; color:green'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 正确选项，你选择了，明智的决定! </span>");
                            }
                        }
                    });

                }

                if (response.action == "wrong") {
                    Notify.danger("抱歉，你答错了!");
                } else if (response.action == "correct") {
                    Notify.success("恭喜你，答对了！");
                }

            });

            $(this).hide();
        });

        $(".modal-footer").on('click', ".next-item", function() {

            $(".quiz-form[name='currentItem']").hide().attr({
                name: "item-in-quiz"
            })
            .next().attr({
                name: "currentItem"
            })
            .find("input[name='user-choices']").attr({
                name: "currentChoice"
            })
            .next("button[name='user-submit']").attr({
                name: "currentSubmit"
            });

            $(".quiz-form[name='currentItem']").show().prev()
                .find("input[name='currentChoice']").attr({
                    name: "user-choices"
                })
                .next("button[type='submit']").attr({
                    name: "user-submit"
                });

            quizItemCount++;

            $(".next-item").hide();
        });

        $(".modal-footer").on('click', ".check-result", function() {
            $.post($(this).data('url'), function(response) {
                $(".quiz-page").replaceWith(response.html);
            }, 'json');
            $(".check-result").remove();
        });

        $(".quiz-page").on('click', ".choice", function(e) {
            $(this).find("input[type='checkbox']").click().change();
            if ($(this).find("input[type='checkbox']").is(":checked") == true) {
                $(this).addClass("empty-item");
            } else {
                $(this).removeClass("empty-item");
            }

        });

        $(".quiz-page").on('click', ".choice", function(e) {
            $(this).find("input[type='radio']").click().change();
            if ($(this).find("input[type='radio']").is(":checked") == true) {
                $(this).addClass("empty-item");
            } else {
                $(this).removeClass("empty-item");
            }

        });

        $(".quiz-page").on('click', "input[type='checkbox']", function(e) {
            e.stopPropagation();
        });

        $(".quiz-page").on('click', "input[type='radio']", function(e) {
            e.stopPropagation();
        });


        $("#modal").off('hide.bs.modal');
        $('#modal').on('hide.bs.modal', function(e) {
            if ($(".quiz-page").find("p[class='empty-item']").text().length > 0) {
                return ;
            }  
            
            if ($(".quiz-page").find(".alreay-lesson-quiz").is(":visible") == true){
                return ;
            }

            if($(".modal-body").find(".check-result-block").length > 0){
                return ;
            }

            if (!confirm("测验内容随机生成，温馨提示：真的要退出本课时的测验吗？")) {
                return false;
            }
        });

    };

});