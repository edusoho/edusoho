define(function(require, exports, module) {

    var BaseChooser = require('./base-chooser-8');
    var Notify = require('common/bootstrap-notify');
    require('jquery.perfect-scrollbar');

    var VideoQualitySwitcher = require('../video-quality-switcher');

    var VideoChooser = BaseChooser.extend({

        qualitySwitcher:null,

    	attrs: {
    		uploaderSettings: {
                file_types : "*.mp4;*.avi;*.flv",
                file_size_limit : "1000 MB",
                file_types_description: "视频文件"
    		},
            preUpload: function(uploader, file) {
                var data = {};
                if (this.qualitySwitcher) {
                    data.videoQuality = this.qualitySwitcher.get('videoQuality');
                    data.audioQuality = this.qualitySwitcher.get('audioQuality');
                    if (this.element.data('hlsEncrypted')) {
                        data.convertor = 'HLSEncryptedVideo';
                    } else {
                        data.convertor = 'HLSVideo';
                    }
                    data.lazyConvert = 1;
                }
                var self = this;
                $.ajax({
                    url: this.element.data('paramsUrl'),
                    async: false,
                    dataType: 'json',
                    data: data, 
                    cache: false,
                    success: function(response, status, jqXHR) {
                        var paramsKey = {};
                        paramsKey.data=data;
                        paramsKey.targetType=self.element.data('targetType');
                        paramsKey.targetId=self.element.data('targetId');

                        response.postParams.paramsKey = JSON.stringify(paramsKey);

                        uploader.setUploadURL(response.url);
                        uploader.setPostParams(response.postParams);
                    },
                    error: function(jqXHR, status, error) {
                        Notify.danger('请求上传授权码失败！');
                    }
                });
            }
    	},

    	events: {
    		'click [data-role=import]': 'onImport'
    	},

    	setup: function() {
    		VideoChooser.superclass.setup.call(this);
            $('#disk-browser-video').perfectScrollbar({wheelSpeed:50});

            if ($('.video-quality-switcher').length > 0) {
                var switcher = new VideoQualitySwitcher({
                    element: '.video-quality-switcher'
                });

                this.qualitySwitcher = switcher;
            }


    	},

    	onImport: function(e) {
            var self = this,
                $btn = $(e.currentTarget),
                $urlInput = this.$('[data-role=import-url]'),
                url = $urlInput.val();

            if (url.length ==0) {
                Notify.danger('请输入视频页面地址');
                return;
            }

            if (!/^[a-zA-z]+:\/\/[^\s]*$/.test(url)) {
                Notify.danger('请输入正确的视频网址');
                return;
            }

            $btn.button('loading');

            $.get($btn.data('url'), {url:url}, function(video){
                var media = {
                    status: 'none',
                    type: video.type,
                    source: video.source,
                    name: video.name,
                    uri: video.files[0].url
                };
                self.trigger('change', media);
                $urlInput.val('');
            }, 'json').error(function(jqXHR, textStatus, errorThrown) {
                Notify.danger('读取视频页面信息失败，请检查您的输入的页面地址后重试');
            }).always(function(){
                $btn.button('reset');
            });

            return;
    	}

    });

    module.exports = VideoChooser;

});


