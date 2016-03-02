define(function(require, exports, module) {

    var Widget = require('widget');
    var Handlebars = require('handlebars');
     require('webuploader');
    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);
    require('es-ckeditor');
    var BatchAttachmentsUploader = require('../../quiz-question/batch-upload-attachments');

    var QuestionCreator = Widget.extend({
        attrs: {
            validator : null,
            form : null,
            stemEditorName: 'Minimal'
        },

        events: {
            'click [data-role=submit]': 'onSubmit',
        },

        setup: function() {
            this.set('enableAudioUpload', $('#question-stem-audio-uploader').length > 0);
            this._initForm();
            this._initStemField();
            this._initAnalysisField();
            
        },

        _initBatchAttachmentUploader: function(editor){

                    
                $("#cloud-btn", "#question-attachment").on('click',function(){
                    var url="";
                    url = $(this).data("html5Url");
                    
                    $("#modal").html('');
                    $("#modal").modal('show');
                    $.get(url, function(html){
                        $("#modal").html(html);
                        var batchAttachmentsUploader = new BatchAttachmentsUploader({
                            editor:editor
                        });
                        

                    });
                })
              
        },


        onSubmit: function(e){
            var submitType = $(e.currentTarget).data('submission');
            this.get('form').find('[name=submission]').val(submitType);
        },

        _initAnalysisField: function() {
            var editor = CKEDITOR.replace('question-analysis-field', {
                toolbar: 'Minimal',
                filebrowserImageUploadUrl: $('#question-analysis-field').data('imageUploadUrl'),
                height: 120
            });

            this.get('validator').on('formValidate', function(elemetn, event) {
                editor.updateElement();
            });


        },

        _initStemField: function() {
            var self = this;
            var height = $('#question-stem-field').height();

            // group: 'default'
            var editor = CKEDITOR.replace('question-stem-field', {
                toolbar: this.get('stemEditorName'),
                filebrowserImageUploadUrl: $('#question-stem-field').data('imageUploadUrl'),
                height: height
            });

            self._uploadAttachment(editor);
            self._initBatchAttachmentUploader(editor);

            this.get('validator').on('formValidate', function(elemetn, event) {
                editor.updateElement();
            });
        },

        _initForm: function() {
            var $form = this.$('[data-role=question-form]');
            this.set('form', $form);
            this.set('validator', this._createValidator($form));
        },

        _createValidator: function($form){
            var self = this;

            Validator.addRule('score',/^(\d){1,10}$/i, '请输入正确的分值');

            validator = new Validator({
                element: $form,
                autoSubmit: false
            });

            validator.addItem({
                element: '#question-stem-field',
                required: true
            });

            validator.addItem({
                element: '#question-score-field',
                required: false,
                rule:'number'
            });

            validator.on('formValidated', function(error, msg, $form) {
                if (error) {
                    return false;
                }

                $('.submit-btn').button('submiting').addClass('disabled');

                self.get('validator').set('autoSubmit',true);
            });

            return validator;
        },

        _uploadAttachment: function(editor){
            var $annexbtn = $('#question-attachment-upload');
            var formData = $.extend({}, {token:$annexbtn.data('uploadToken')});
            var uploadUrl = $annexbtn.data('url');
            var uploader = WebUploader.create({
               swf: require.resolve("webuploader").match(/[^?#]*\//)[0] + "Uploader.swf",
               server: uploadUrl,
               pick: $annexbtn,
               formData: $.extend(formData , {'_csrf_token': $('meta[name=csrf-token]').attr('content') }),
               accept: {
                       title: 'Attachment',
                       extensions: 'mp4,avi,flv,wmv,mov,m4v,mp3,doc,docx,pdf,ppt,pptx,swf',
                }
            });

            uploader.on( 'fileQueued', function( file ) {
                Notify.info('正在上传，请稍等！', 0);
                uploader.upload();
            });

            uploader.on( 'uploadSuccess', function( file, response ) {
                    editor.insertHtml(response._raw);
                    Notify.success('附件上传成功！');
            });

            uploader.on( 'uploadError', function( file, response ) {
                Notify.danger('上传失败，请重试！');
            });
        }

    });

    module.exports = QuestionCreator;
});