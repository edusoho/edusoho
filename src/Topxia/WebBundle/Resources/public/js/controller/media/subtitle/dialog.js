define(function(require, exports, module) {


    require('new-uploader');
    var Widget = require('widget');
    var Notify = require('common/bootstrap-notify');

    var SubtitleDialog = Widget.extend({
        uploader: null,
        media: null,
        attrs:{
            subtitleUploader:$('#subtitle-uploader')
        },
        events: {
            'click .js-subtitle-delete':'subtitleDelete'
        },
        setup: function(){
            var self = this;
            $('#media-choosers .file-chooser-tabs [data-toggle="tab"]').on('show.bs.tab', function(e) {
                var $target = $(e.target);
                if ($target.attr('href') == '#video-chooser-import-pane') {
                    self.hide();
                } else {
                    self.show();
                }
            });
        },
        renderHTML: function(media)
        {
            if (media && 'id' in media && media.id > 0) {
                this.media = media;
                this.element.html('加载字幕...');
                var self = this;
                $.get(this.element.data('dialogUrl'), {mediaId:this.media.id}, function(html){
                    self.element.html(html);
                    self.initUploader();
                });
            }
        },
        show: function()
        {
            var parent = this.element.parent('.form-group');
            if (parent.length > 0) {
                parent.removeClass('hide');
            }
        },
        hide: function()
        {
            var parent = this.element.parent('.form-group');
            if (parent.length > 0) {
                parent.addClass('hide');
            }
        },
        initUploader: function()
        {
            var _self = this;
            var $elem = this.$('#subtitle-uploader');
            var mediaId = this.$('.js-subtitle-dialog').data('mediaId');
            var globalId = $elem.data('mediaGlobalId');
        
            if (this.uploader) {
                this._destoryUploader();
            }
            var uploader = new UploaderSDK({
                initUrl:$elem.data('initUrl'),
                finishUrl:$elem.data('finishUrl'),
                id:'subtitle-uploader',
                ui:'simple',
                multi:true,
                accept:{
                    extensions:['srt'],
                    mimeTypes:['text/srt']
                },
                type:'sub',
                process:{
                    videoNo:globalId,
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
                    "mediaId": mediaId
                }).success(function (data) {
                    var convertStatus = require('./convert-status-map');
                    $('.js-media-subtitle-list').append('<li class="pvs">'+
                            '<span class="subtitle-name prl">'+data.name+'</span>'+
                            '<span class="subtitle-transcode-status '+data.convertStatus+'">'+ convertStatus(data.convertStatus) +'</span>'+
                            '<a href="javascript:;" class="btn-link pll color-primary js-subtitle-delete" data-subtitle-delete-url="/media/'+ mediaId+'/subtitle/'+ data.id +'/delete">删除</a>'+
                        '</li>');
                    if($('.js-media-subtitle-list li').length > 3){
                        $('#subtitle-uploader').hide();
                    }
                    Notify.success(Translator.trans('字幕上传成功！'));
                }).error(function (data){
                    Notify.danger(Translator.trans(data.responseJSON.error.message));
                });
            });

            this.uploader = uploader;
        },
        subtitleDelete: function(e){
            var _self = this;
            var $elem = $(e.currentTarget);
            $.post($elem.data('subtitleDeleteUrl'),function(data){
                if(data){
                    Notify.success(Translator.trans('删除字幕成功'));
                    $elem.parent().remove();
                    $('#subtitle-uploader').show();
                }
            });
        },
        _destoryUploader: function() {
            if (!this.uploader) {
                return ;
            }
            this.uploader.__events = null;
            try {
               this.uploader.destroy(); 
            } catch(e) {
                //忽略destory异常
            }
            this.uploader = null;
        },

        destroy: function () {
            this._destoryUploader();
            SubtitleDialog.superclass.destroy.call(this);
        }
    });

    module.exports = SubtitleDialog;
});