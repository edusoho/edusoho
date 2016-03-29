define(function(require, exports, module) {
    exports.run = function() {
        var btn = $('#submitQuestion');
        var data = $("#data");
        var markerId = data.data('markerid');
        var questionId = data.data('questionid');
        var questionType = data.data('type');
        var lessonId = data.data('lessonid');
        btn.on('click', function() {
            var answer = doMarkerQuestion(questionType);
            if(!answer|| answer.length==0){
                $('.text-danger').html(Translator.trans('请先作答!'));
                setTimeout(clearHtml,1500);
                return;
            }
            $.get(data.data('url'), {
                "markerId": markerId,
                "questionId": questionId,
                "answer": answer,
                "type": questionType,
                "lessonId": lessonId
            }, function(result) {
                $.get(data.data('show-questionanswer'), {
                    "markerId": markerId,
                    "questionId": questionId,
                    "questionMarkerResultId": result.questionMarkerResultId
                }, function(data) {
                    var $modal = $("#modal");
                    $modal.html(data);
                    var $modaldialog = $("#modal").find('.modal-dialog');
                    var $player = $(document.getElementById('viewerIframe').contentDocument);
                    //判断是否全屏
                    if ($player.width() == $('body').width()) {
                        $modal.css('z-index', '2147483647');
                    } else {
                        var $modaldialog = $modal.find('.modal-dialog');
                        $modaldialog.css('margin-left', ($('body').width() - $('.toolbar').width() - $modaldialog.width()) / 2);
                    }
                    $modal.show();
                });
            });
        });
        $(".marker-modal .close").on('click', function(e) {
            $(this).closest('#modal').hide();
            e.preventDefault();
        });

        $(".marker-modal .question-single_choice li,.marker-modal .question-determine li").on('click', function() {
            var $this = $(this);
            var $typecheck = $(this).find('.type-check').addClass('active');
            $this.siblings().find(".type-check").removeClass("active");
        });

        $(".marker-modal .question-uncertain_choice li").on("click", function() {
            var $this = $(this);
            var $typecheck = $(this).find('.type-check').toggleClass('active');
        });

        $(".marker-modal .question-choice li").on("click", function() {
            var $this = $(this);
            var $typecheck = $(this).find('.type-check').toggleClass('active');
        });
        function clearHtml(){
            $('.text-danger').html("");
        };
        var doMarkerQuestion = function(type) {
            switch (type) {
                case "single_choice":
                    return doSingleChoice();
                    break;
                case "uncertain_choice":
                    return doUncertainChoice();
                    break;
                case "determine":
                    return doDetermine();
                    break;
                case "fill":
                    return doFill();
                    break;
                case "choice":
                    return doChoice();
                    break;
                default:
                    break;
            };

            function doSingleChoice() {
                var answer = null;
                answer = $("span.type-check.active").attr('value');
                return answer;
            };

            function doUncertainChoice() {
                var answers = [];
                $("span.type-check.active").each(function() {
                    answers.push($(this).attr('value'));
                });
                return answers;
            };

            function doDetermine() {
                var answer = null;
                answer = $("span.type-check.active").attr('value');
                return answer;
            };

            function doFill() {

                var answers = [];
                $("input[name='answer[" + questionId + "][]']").each(function() {
                    answers.push($(this).val());
                    if($(this).val() == ""){
                        return answers=false;
                    }
                });
                return answers;
            };
            function doChoice() {
                var answers = [];
                $("span.type-check.active").each(function() {
                    answers.push($(this).attr('value'));
                });
                return answers;
            };
        };
    }
});