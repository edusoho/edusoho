define(function(require, exports, module) {

    var BaseChooser = require('./base-chooser');
    var Notify = require('common/bootstrap-notify');

    var VideoChooser = BaseChooser.extend({
    	attrs: {
    		uploaderSettings: {
                file_types : "*.mp4;*.avi;*.flv",
                file_size_limit : "300 MB",
                file_types_description: "视频文件"
    		}
    	},

    	events: {
    		'click [data-role=import]': 'onImport'
    	},

    	setup: function() {
    		VideoChooser.superclass.setup.call(this);
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
                self.trigger('change', video);
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


