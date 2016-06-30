define(function(require, exports, module) {
    require('webuploader2');
    var store = require('store');
    var BaseChooser = require('./base-chooser-8');
    var Notify = require('common/bootstrap-notify');
    require('jquery.perfect-scrollbar');


    var VideoChooser = BaseChooser.extend({

        qualitySwitcher:null,

    	attrs: {
    		uploaderSettings: {
                file_types : "*.mp4;*.avi;*.flv;*.wmv;*.mov;*.m4v;*.mpg",
                file_size_limit : "2048 MB",
                file_types_description: "视频文件"
    		},
        },

        getProcess: function() {
            return {
                videoQuality: $('.video-quality-switcher').find('input[name=video_quality]:checked').val(), 
                audioQuality: $('.video-quality-switcher').find('input[name=video_audio_quality]:checked').val(),
                supportMobile: $('.video-quality-switcher').find('input[name=support_mobile]').val()
            };
        },

    	events: {
    		'click [data-role=import]': 'onImport'
    	},

    	setup: function() {
    		VideoChooser.superclass.setup.call(this);
            $('#disk-browser-video').perfectScrollbar({wheelSpeed:50});
    	},

    	onImport: function(e) {
            var self = this,
                $btn = $(e.currentTarget),
                $urlInput = $btn.parent().siblings('input'),
                url = $urlInput.val();

            if (url.length == 0 ) {
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
