define(function(require, exports, module) {

    var BaseChooser = require('./base-chooser-2');
    var Notify = require('common/bootstrap-notify');
    require('jquery.perfect-scrollbar');

    var VideoChooser = BaseChooser.extend({
    	attrs: {
    		uploaderSettings: {
                file_types : "*.mp4;*.avi;*.flv",
                file_size_limit : "1000 MB",
                file_types_description: "视频文件"
    		}
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
                $urlInput = this.$('[data-role=import-url]'),
                url = $urlInput.val();

            if (url.length ==0) {
                Notify.danger('请输入视频页面地址');
                return;
            }

            if (!/^[a-zA-z]+:\/\/[^\s]*$/.test(url)) {
                Notify.danger('请输入视频页面地址格式不正确');
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
                self.trigger('fileinfo.fetched', {});
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


