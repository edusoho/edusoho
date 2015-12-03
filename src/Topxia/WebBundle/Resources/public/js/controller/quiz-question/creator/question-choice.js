define(function(require, exports, module) {

    var BaseQuestion = require('./question-base');
    var Handlebars = require('handlebars');
    var Notify = require('common/bootstrap-notify');
    require('es-ckeditor');
    var ChoiceQuestion = BaseQuestion.extend({
        attrs: {
            globalId: 1
        },

        events: {
            'click [data-role=add-choice]': 'onAddChoice',
            'click [data-role=delete-choice]': 'onDeleteChoice'
        },
        
        setup: function() {
            ChoiceQuestion.superclass.setup.call(this);
            this._initValidator();
            this._setupForChoice();
        },
            
        onAddChoice: function(event) {
            var choiceCount = this.$('[data-role=choice]').length;
            if (choiceCount >= 10) {
                Notify.danger("选项最多十个!");
                return false;
            }
            var choiceCount = this.$('[data-role=choice]').length;
            var code = String.fromCharCode(choiceCount + 65);
            var model = {code: code, id:this._generateNextGlobalId(), imageUploadUrl:$('#ckeditor-config').data('imageUploadUrl'), imageDownloadUrl:$('#ckeditor-config').data('imageDownloadUrl')}
            this.addChoice(model);
        },

        onDeleteChoice: function(event) {
            var choiceCount = this.$('[data-role=choice]').length;
            if (choiceCount <= 2 ) {
                Notify.danger("选项至少二个!");
                return false;
            }
            this.deleteChoice(event);
        },

        addChoice: function(model) {
            var self = this;
            var template = this.get('choiceTemplate');
            var $html = $($.trim(template(model)));

            if (this.get('enableAudioUpload')) {
                $html.find('.item-audio-upload').removeClass('hide');
            }

            $html.appendTo(this.$('[data-role=choices]'));

            var editor = CKEDITOR.replace('item-input-'+model.id, {
                toolbar: 'Minimal',
                filebrowserImageUploadUrl: $('#item-input-'+model.id).data('imageUploadUrl'),
                height: 120
            });

            this.get('validator').on('formValidate', function(elemetn, event) {
                editor.updateElement();
            });

            this.get("validator").addItem({
                element: '#item-input-'+model.id,
                required: true
            });
        },

        deleteChoice: function(e){
            var $btn = $(e.currentTarget);
            var id = '#' + $btn.parents('.form-group').find('.item-input').attr('id');
            this.get('validator').removeItem(id);
            $btn.parents('[data-role=choice]').remove();
            this.$('[data-role=choice]').each(function(index, item){
                $(this).find('.choice-label').html('选项' + String.fromCharCode(index + 65));
            });
        },


        _prepareFormData: function(){
            var answers = [],
            $form = this.get('form');
            $form.find(".answer-checkbox").each(function(index){
                if($(this).prop('checked')) {
                    answers.push(index);
                }
            });
            if (0 == answers.length){
                Notify.danger("请选择正确答案!");
                return false;
            }

            $.each(answers, function(i, answer) {
                $form.append('<input type="hidden" name="answer[]" value="' + answer + '">');
            });

            return true;
        },

        _initValidator: function(){
            var self = this;
            this.get('validator').off('formValidated');
            this.get('validator').on('formValidated', function(error, msg, $form) {
                if (error) {
                    return false;
                }
                if(!self._prepareFormData()){
                    return false;
                }

                $('.submit-btn').button('submiting').addClass('disabled');

                self.get('validator').set('autoSubmit',true);
            });
        },

        _setupForChoice: function() {
            var self = this;
            var choiceTemplate = Handlebars.compile(this.$('[data-role=choice-template]').html());
            this.set('choiceTemplate', choiceTemplate);

            var choicesData = this.$('[data-role=choices-data]').html();

            if ($.type(choicesData) != 'undefined') {
                var choices = $.parseJSON(choicesData);
                var answers = $.parseJSON(this.$('[data-role=answers-data]').html());

                $.each(choices, function(index, choiceContent) {
                    self.addChoice({
                        code: String.fromCharCode(index + 65),
                        id: self._generateNextGlobalId(),
                        content:choiceContent,
                        isAnswer: $.inArray(index+'', answers) != -1,
                        imageUploadUrl:$('#ckeditor-config').data('imageUploadUrl'), 
                        imageDownloadUrl:$('#ckeditor-config').data('imageDownloadUrl')
                    });
                });
            } else {
                for (var i = 0; i < 4; i++) {
                    this.addChoice({
                        code: String.fromCharCode(i + 65),
                        id: self._generateNextGlobalId(),
                        imageUploadUrl:$('#ckeditor-config').data('imageUploadUrl'), 
                        imageDownloadUrl:$('#ckeditor-config').data('imageDownloadUrl')
                    });
                }
            }
        },

        _generateNextGlobalId: function() {
            var globalId = this.get('globalId');
            this.set('globalId', globalId + 1);
            return globalId;
        }


    });

    module.exports = ChoiceQuestion;

});


