define(function(require, exports, module) {

    var BaseQuestion = require('./question-base');
    var Handlebars = require('handlebars');
    var Notify = require('common/bootstrap-notify');
    require('webuploader');

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
            var model = {code: code, id:this._generateNextGlobalId()}
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
            this.get("validator").addItem({
                element: '#item-input-'+model.id,
                required: true
            });



            var uploader = WebUploader.create({
                swf: require.resolve("webuploader").match(/[^?#]*\//)[0] + "Uploader.swf",
                server: this.element.data('uploadUrl'),
                pick: '#item-upload-' + model.id,
                formData: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
                accept: {
                    title: 'Images',
                    extensions: 'gif,jpg,jpeg,png',
                    mimeTypes: 'image/*'
                }
            });

            uploader.on( 'fileQueued', function( file ) {
                Notify.info('正在上传，请稍等！', 0);
                uploader.upload();
            });

            uploader.on( 'uploadSuccess', function( file, response ) {
                var result = '[image]' + response.hashId + '[/image]';
                var $input = $($('#item-upload-' + model.id).data('target'));
                $input.val($input.val() + result);
                Notify.success('上传成功！', 1);
            });

            uploader.on( 'uploadError', function( file, response ) {
                Notify.danger('上传失败，请重试！');
            });


            if (this.get('enableAudioUpload')) {
                /**
                 * 音频上传
                 */
                var audioUploader = WebUploader.create({
                    swf: require.resolve("webuploader").match(/[^?#]*\//)[0] + "Uploader.swf",
                    pick: '#item-audio-upload-' + model.id,
                    formData: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
                    accept: {
                        title: 'Audio',
                        extensions: 'mp3,wav',
                        mimeTypes: 'audio/*'
                    }
                });

                audioUploader.on( 'fileQueued', function( file, a, b ) {
                    Notify.info('正在上传，请稍等！', 0);

                    $.ajax({
                        url: self.element.data('mediaUploadParamsUrl'),
                        async: false,
                        dataType: 'json',
                        data: {convertor: 'audio'},
                        cache: false,
                        success: function(response, status, jqXHR) {
                            audioUploader.option('server', response.url)
                            audioUploader.option('formData', response.postParams);
                        },
                        error: function(jqXHR, status, error) {
                            Notify.danger('请求上传授权码失败！');
                        }
                    });

                    audioUploader.upload();
                });

                audioUploader.on( 'uploadSuccess', function( file, response ) {
                    Notify.success('上传成功！', 1);

                    $.post(self.element.data('mediaUploadCallbackUrl'), response, function(response) {
                        var name = response.filename.match(/[^\.]*\./)[0].slice(0, -1);
                        var result = '[audio id="' + response.id +'"]' + name + '[/audio]';

                        var $input = $($('#item-upload-' + model.id).data('target'));
                        $input.val($input.val() + result);
                    });

                });

                audioUploader.on( 'uploadError', function( file, response ) {
                    Notify.danger('上传失败，请重试！');
                });
            }
            

        },

        deleteChoice: function(e){
            var $btn = $(e.currentTarget);
            var id = '#' + $btn.parents('.form-group').find('input.item-input').attr('id');

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
                        isAnswer: $.inArray(index+'', answers) != -1
                    });
                });
            } else {
                for (var i = 0; i < 4; i++) {
                    this.addChoice({
                        code: String.fromCharCode(i + 65),
                        id: self._generateNextGlobalId()
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


