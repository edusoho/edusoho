define(function(require, exports, module) {

    require('new-uploader');
    var Widget = require('widget');

    var SubtitleDialog = Widget.extend({
        uploader: null,
        events: {
        },
        setup: function(){
            this.initUploader();
        },
        initUploader: function()
        {
            var $elem = $('#subtitle-uploader');
            var uploader = new UploaderSDK({
                initUrl:$elem.data('initUrl'),
                finishUrl:$elem.data('finishUrl'),
                id:'uploader',
                ui:'simple',
                multi:true,
                accept:{
                    extensions:['srt'],
                    mimeTypes:['text/srt']
                },
                type:'sub',
                process:{
                    videoNo:$elem.data('mediaGlobalId'),
                }
            })
            uploader.on('error',function(err){
                if(err.error === 'Q_TYPE_DENIED'){
                    Notify.danger(Translator.trans('请上传srt格式的文件！'));
                }
            });
            uploader.on('file.finish', function (file) {
                $.post($elem.data('subtitleCreateUrl'), {
                    "name": file.name,
                    "subtitleId": file.id,
                    "mediaId": 69
                }).success(function (data) {
                    Notify.success(Translator.trans('字幕上传成功！'));
                }).error(function (data){
                    Notify.danger(Translator.trans(data.responseJSON.error.message));
                });
            });

            this.uploader = uploader;
        }
    });

    module.exports = SubtitleDialog;
});