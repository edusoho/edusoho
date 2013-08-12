define(function(require, exports, module) {

    require('ckeditor');
    var Notify = require('common/bootstrap-notify');
    var Widget = require('widget');
    var Validator = require('bootstrap.validator');

    var QuizModalWiget = Widget.extend({

        attrs: {
            form : '#quiz-form',
            validator : null
        },
        
        events: {
            'click [data-role=quiz-item-delete]': 'deleteQuizItem',
            'click [data-role=quiz-item-edit]': 'editQuizItem',
            'click [data-role=option-delete]': 'deleteItemOption',
            'click [data-role=quiz-item-add]': 'clearQuizOptions',
            'click [data-role=option-add]': 'addQuizOption'
        },

        setup: function() {
            var validator = this._createValidator(this.get('form'));
            this.set('validator', validator);
            this._replaceCKeditor();
            this._updateCKeditor();
            for (var i = 0; i < 4; i++) {
                this.addQuizOption();
            };
        },

        editQuizItem: function(e){

            var self = this;
            var $btn = $(e.currentTarget);

            $(".created-quiz-items").find("a").each(function(index){
                $(this).removeClass("active");
            });
            $btn.addClass("active");

            $('#quiz-form').find('.options').children().each(function(index){
                var id = $(this).find('.item-input').attr("id");
                self.get("validator").removeItem('#'+id);
            });

            $('#quiz-form').find('.options').html('');
            $.post($btn.data('url'), function(response) {
                var arrayChoices = response.lessonQuizItem.choices;
                var arrayAnswers=response.lessonQuizItem.answers.split(";");
                for (var i=0; i<arrayChoices.length; i++)
                {
                    self.addQuizOption().find('.item-input').val(arrayChoices[i]);
                }

                $('#quiz-form').find("input[name='answer-checkbox']").each(function(index){
                    if(arrayAnswers.indexOf(index.toString()) != -1){
                        $(this).attr("checked", true);
                    }
                });

                CKEDITOR.instances['quiz_description'].setData(response.lessonQuizItem.description);
                $("#quiz-form").attr({action:"/quiz/item/update/"+response.lessonQuizItem.id});
                var level = response.lessonQuizItem.level;

                switch(level)
                {
                case "low":
                  $("[type=radio][value='low']").click().change();
                  break;
                case "normal":
                  $("[type=radio][value='normal']").click().change();
                  break;
                case "high":
                  $("[type=radio][value='high']").click().change();
                  break;
                default:
                  $("[type=radio][value='normal']").click().change();
                }

            }, 'json');
        },

        deleteQuizItem: function(e){
            var $btn = $(e.currentTarget);
            $.post($btn.data('url'), function(response) {
                $btn.parent().remove();
            }, 'json');
            if($('.created-quiz-items').children().length == 1){
                $('.notice-quiz-items').text("暂时没有本课程的测验题!");
            }
        },

        clearQuizOptions: function(e){
            var self = this;
            Notify.success("增加了一道新的测验题目!");

            $(".created-quiz-items").find("a").each(function(index){
                    $(this).removeClass("active");
            });
            $('#quiz-form').find('.options').children().each(function(index){
                var id = $(this).find('.item-input').attr("id");
                self.get("validator").removeItem('#'+id);
            });
            $("[type=radio][value='normal']").click().change();
            $('#quiz-form').find('.options').html('');
            $("#quiz-form").attr({action:
                "/course/"+$('#courseId').val()+"/lesson/"+$('#lessonId').val()+"/quiz/create"});
            CKEDITOR.instances['quiz_description'].setData('');
            for (var i = 0; i < 4; i++) {
                this.addQuizOption();
            };
        },

        addQuizOption: function(e){

            var length = $('#quiz-form').find('.options').children().length;
            var template = $('#option-template').clone(true).show();

            template.attr({id:"option-"+String.fromCharCode(length+97)});
            template.find('label').text(String.fromCharCode(length+65)+"选项")
                .attr({for:"item-input-"+String.fromCharCode(length+97)});  
            template.find('.item-input-template').attr({id:"item-input-"+String.fromCharCode(length+97)})
                .attr({class:"item-input"});
            template.find('[name=answer-checkbox]').data('role',String.fromCharCode(length+97));
            
            template.appendTo(".options");

            var id = template.find('.item-input').attr("id");

            this.get("validator").addItem({
                element: '#'+id,
                required: true
            });

            return template;
        },

        deleteItemOption: function(e){
            var self = this;

            if($('#quiz-form').find('.options').children().length == 2){
                Notify.danger("每道题目的选项不得少于两个!");
                return false;
            }

            $('#quiz-form').find('.options').children().each(function(index){
                var id = $(this).find('.item-input').attr("id");
                self.get("validator").removeItem('#'+id);
            });

            $(e.currentTarget).parent().remove();

            $('#quiz-form').find('.options').children().each(function(index){
                $(this).attr({id:"option-"+String.fromCharCode(index+97)});
                $(this).find('label').text(String.fromCharCode(index+65)+"选项")
                    .attr({for:"item-input-"+String.fromCharCode(index+97)});
                $(this).find('.item-input').attr({id:"item-input-"+String.fromCharCode(index+97)});
                $(this).find('[name=answer-checkbox]').data('role',String.fromCharCode(index+97));

                var id = $(this).find('.item-input').attr("id");
                self.get("validator").addItem({
                    element: '#'+id,
                    required: true
                });

            });   
        },

        _replaceCKeditor: function(e){
            CKEDITOR.replace('quiz_description', {
                height: 100,
                resize_enabled: false,
                forcePasteAsPlainText: true,
                toolbar: 'Simple',
                removePlugins: 'elementspath',
                filebrowserUploadUrl: '/ckeditor/upload?group=course'
            });
        },

        _updateCKeditor: function(e){
            validator.on('formValidate', function(elemetn, event) {
                CKEDITOR.instances['quiz_description'].updateElement();
            });
        },

        _setChoicesAndAnswers: function(e){
            var answers = "";
            $('#quiz-form').find("input[name='answer-checkbox']").each(function(index){
                if($(this).is(":checked") == true){
                    answers += index +";";
                }
            });
            answers=answers.substr(0,answers.length-1);

            if (0 == answers.length){
                Notify.danger("您尚未选择正确答案,请选择正确答案,或事先增加新的选项!");
                return false;
            }
            $('#quiz-form').find('#quiz_answers').attr({value:answers});
        },

        _addValidatorItem: function($validator){
            $validator.addItem({
                element: '[name="quiz[description]"]',
                required: true
            });
        },

        _createValidator: function($form){
            var self = this;

            validator = new Validator({
                element: $form,
                autoSubmit: false
            });

            this._addValidatorItem(validator);
            
            validator.on('formValidated', function(error, msg, $form) {

                if (error) {
                    return;
                }

                if(self._setChoicesAndAnswers() == false){
                    return false;
                }

                $.post($form.attr('action'), $form.serialize(), function(response){

                    if ((response.action == 'create') && (response.status == 'ok')){
                        if($('.created-quiz-items').children().length == 0){
                          $('.notice-quiz-items').text("已添加的测验题目:");  
                        }

                        $(".created-quiz-items").append(response.html);
                        self.clearQuizOptions();
                        $(".created-quiz-items").find("a").each(function(index){
                            $(this).removeClass("active");
                        });
                    }

                    if((response.action == 'update') && (response.status == 'ok')){
                        $('#quiz-item-id-'+response.quizItem.id).find('p').text(response.quizItem.description);
                    }
                    
                    $("[type=radio][value='normal']").click().change();
                    Notify.success("保存成功!");
                }, 'json');
            });
            return validator;
        }

    });
    
    module.exports = QuizModalWiget;
});