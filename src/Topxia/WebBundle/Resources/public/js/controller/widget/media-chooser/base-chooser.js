define(function(require, exports, module) {

    require('swfupload');
    var Widget = require('widget');
    var DiskBrowser = require('../disk-browser');
    var Notify = require('common/bootstrap-notify');

    var BaseChooser = Widget.extend({
        attrs: {
            choosed: null,
            uploader: null,
            uploaderSettings: {},
        },

        events: {
            "click [data-role=trigger]": "open"
        },

        setup: function() {
            this._chooses = {};
            this.on('change', this.onChanged);

            var choosed = this.get('choosed');
            if (choosed) {
                this.trigger('change', choosed);
            }

            this._initTabs();

            this._initDiskBrowser();
            this._initUploadPane();
        },

        open: function() {
            this.element.find(".media-chooser-bar").hide();
            this.element.find(".media-chooser-main").show();
            return this;
        },

        show: function() {
            this.element.show();
            return this;
        },

        close: function() {
            this.element.find(".media-chooser-main").hide();
            this.element.find(".media-chooser-bar").show();
            this.get('uploaderProgressbar').reset().hide();
            return this;
        },

        hide: function() {
            this.element.hide();
            return this;
        },

        isUploading: function() {
            return this.get('uploaderProgressbar').isProgressing();
        },

        onChanged: function(item) {
            if (item) {
                var html = '';
                if (item.page) {
                    html = '<a href="' + item.page + '" target="_blank">' + item.name + '</a>';
                } else {
                    html = item.name;
                }
                this.element.find('[data-role=placeholder]').html(html);
                this.close();
            }
        },

        _initTabs: function() {
            var self = this;
            this.$('.media-chooser-tabs [data-toggle="tab"]').on('show.bs.tab', function(e) {
                if ($(e.target).hasClass('media-chooser-uploader-tab')) {
                    self.get('uploaderProgressbar').reset().hide();
                }

                if ($(e.relatedTarget).hasClass('media-chooser-uploader-tab')) {
                    if (self.isUploading()) {
                        return confirm('当前正在上传文件，离开此页面，将自动取消上传。您真的要离开吗？');
                    }
                }
            });
        },

        _initDiskBrowser: function() {
            var self = this;

            var browser = new DiskBrowser({
                element: this.$('[data-role=disk-browser]')
            }).show();

            browser.on('select', function(file) {
                self.trigger('change', self._convertFileToMedia(file));
            });
        },

        _convertFileToMedia: function(file) {
            var media = {};
            media.type = file.type;
            media.source = 'self';
            media.name = file.filename;
            media.files = [
                {url: file.uri, type: 'mp4'}
            ];

            return media;
        },

        _initUploadPane: function() {
            var $btn = this.$('[data-role=uploader-btn]');
            var progressbar = new UploadProgressBar($btn.data('progressbar'));

            this.set('uploaderProgressbar', progressbar);
            this.set('uploader', this._createSWFUpload($btn, progressbar));
        },

        _createSWFUpload: function($btn, progressbar) {
            var self = this;

            var settings = $.extend({}, {
                upload_url : $btn.data('url'),
                post_params : {
                    "key" : $btn.data('key'),
                    "token" : $btn.data('token'),
                    "x:filepath": $btn.data('filepath')
                },
                file_types : "*.*",
                file_size_limit : "10 MB",
                file_upload_limit : 1,
                file_queue_limit: 1,
                file_post_name: 'file',

                button_placeholder_id : $btn.attr('id'),
                button_width: "75",
                button_height: "35",
                button_text: "<span class=\"btnText\">上传</span>",
                button_text_style : ".btnText { color: #333; font-size:16px;}",
                button_text_left_padding : 18,
                button_text_top_padding : 5,
                button_image_url: $btn.data('buttonImage'),

                file_dialog_complete_handler: function(numFilesSelected, numFilesQueued) {
                    if (numFilesSelected == 0) {
                        return;
                    }
                    if (numFilesSelected > 1) {
                        Notify.danger('一次只能上传一个文件，请重新选择。');
                        return ;
                    }

                    if (numFilesQueued == 0) {
                        Notify.info('文件正在上传中，请等待本次上传完毕后，再上传。');
                        return ;
                    }
                    this.startUpload();
                },

                upload_start_handler: function(file) {
                    progressbar.reset().show();
                },

                upload_progress_handler: function(file, bytesLoaded, bytesTotal) {
                    var percentage = Math.ceil((bytesLoaded / bytesTotal) * 100);
                    progressbar.setProgress(percentage);
                },

                upload_error_handler: function(file, errorCode, message) {
                    Notify.danger('文件上传失败，请重试！');
                },

                upload_success_handler: function(file, serverData) {
                    progressbar.setComplete().hide();
                    console.log(serverData);
                    serverData = $.parseJSON(serverData);

                    if ($btn.data('callback')) {
                        $.post($btn.data('callback'), serverData, function(response) {
                            var media = self._convertFileToMedia(response);
                            self.trigger('change',  media);
                            Notify.success('文件上传成功！');
                        }, 'json');
                    } else {
                        var media = self._convertFileToMedia(serverData);
                        self.trigger('change',  media);
                        Notify.success('文件上传成功！');
                    }


                }
            }, this.get('uploaderSettings'));

            if ($btn.data('filetypes')) {
                settings.file_types = $btn.data('filetypes');
            }

            var swfu = new SWFUpload(settings);

            return swfu;
        }

    });

    function UploadProgressBar(element) {
        this.element = $(element);
        this.percentage = 0;
    }

    UploadProgressBar.prototype.show = function () {
        this.element.show();
        return this;
    }

    UploadProgressBar.prototype.hide = function () {
        this.element.hide();
        return this;
    }

    UploadProgressBar.prototype.setProgress = function (percentage) {
        this.percentage = percentage;
        this.element.find('.progress-bar').css('width', percentage + '%');
        return this;
    }

    UploadProgressBar.prototype.setComplete = function () {
        this.setProgress(100);
        return this;
    }

    UploadProgressBar.prototype.reset = function () {
        this.setProgress(0);
        return this;
    }

    UploadProgressBar.prototype.isProgressing = function () {
        return this.percentage > 0;
    }

    module.exports = BaseChooser;
});