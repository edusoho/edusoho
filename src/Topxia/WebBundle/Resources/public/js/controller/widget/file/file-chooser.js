define(function(require, exports, module) {

    var plupload = require('plupload');
    var Widget = require('widget');
    var FileBrowser = require('./file-browser');
    var Notify = require('common/bootstrap-notify');

    var FileChooser = Widget.extend({
        attrs: {
            choosed: null,
            uploader: null,
            progressbar: null,
            uploaderSettings: {},
        },

        events: {
            "click [data-role=trigger]": "open"
        },

        setup: function() {
            this._chooses = {};
            this.on('change', this.onChanged);

            this._initTabs();
            this._initUploadPane();
            this._initFileBrowser();

            var choosed = this.get('choosed');
            if (choosed) {
                this.trigger('change', choosed);
                this.trigger('fileinfo.fetched', {});
            }

        },

        open: function() {
            this.element.find(".file-chooser-bar").hide();
            this.element.find(".file-chooser-main").show();
            return this;
        },

        show: function() {
            this.element.show();
            return this;
        },

        close: function() {
            this.element.find(".file-chooser-main").hide();
            this.element.find(".file-chooser-bar").show();
            this.get('progressbar').reset().hide();
            return this;
        },

        hide: function() {
            this.element.hide();
            return this;
        },

        isUploading: function() {
            return this.get('progressbar').isProgressing();
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
                if (item.status == 'waiting') {
                    this.element.find('[data-role=waiting-tip]').show();
                } else {
                    this.element.find('[data-role=waiting-tip]').hide();
                }
                this.close();
            }
        },

        _initTabs: function() {
            var self = this;
            this.$('.file-chooser-tabs [data-toggle="tab"]').on('show.bs.tab', function(e) {
                if ($(e.target).hasClass('file-chooser-uploader-tab')) {
                    self.get('progressbar').reset().hide();
                }

                if ($(e.relatedTarget).hasClass('file-chooser-uploader-tab')) {
                    if (self.isUploading()) {
                        return confirm('当前正在上传文件，离开此页面，将自动取消上传。您真的要离开吗？');
                    }
                }
            });
        },

        _initFileBrowser: function() {
            var self = this;

            var browser = new FileBrowser({
                element: this.$('[data-role=file-browser]')
            }).show();

            browser.on('select', function(file) {
                self.trigger('change', self._convertFileToMedia(file));
                self.trigger('fileinfo.fetched', {});
            });
        },

        _convertFileToMedia: function(file) {
            var media = {};
            media.id = file.id ? file.id : 0;
            media.status = file.convertStatus ? file.convertStatus : 'none';
            media.type = file.type;
            media.source = 'self';
            media.name = file.filename;
            return media;
        },

        _initUploadPane: function() {
            var $btn = this.$('[data-role=uploader-btn]'),
                progressbar = new UploadProgressBar(this.$('[data-role=progress]'));

            this.set('progressbar', progressbar);
            this.set('uploader', this._createUploader($btn, progressbar));
        },

        _createUploader: function($btn, progressbar) {
            var self = this;
            var btnData = $btn.data();

            var uploader = new plupload.Uploader({
                runtimes : 'flash',
                max_file_size: '2gb',
                browse_button : $btn.attr('id'),
                url : btnData.uploadUrl
            });

            uploader.bind('FilesAdded', function(uploader, files) {
                console.log('FilesAdded', uploader);
                uploader.refresh();
                setTimeout(function(){
                    uploader.start();
                }, 1);
            });

            uploader.bind('BeforeUpload', function(uploader, file) {
                $.ajax({
                    url: btnData.paramsUrl,
                    async: false,
                    dataType: 'json',
                    cache: false,
                    success: function(response, status, jqXHR) {
                        uploader.settings.url = response.url;
                        uploader.settings.multipart_params = response.postParams;
                        uploader.refresh();
                    },
                    error: function(jqXHR, status, error) {
                        Notify.danger('请求上传授权码失败！');
                        uploader.stop();
                    }
                });
                progressbar.reset().show();
            });

            uploader.bind('UploadProgress', function(uploader, file) {
                progressbar.setProgress(file.percent);
            });

            uploader.bind('FileUploaded', function(uploader, file, response) {
                progressbar.setComplete().hide();
                response = $.parseJSON(response.response);

                if (btnData.callback) {
                    $.post(btnData.callback, response, function(response) {
                        var media = self._convertFileToMedia(response);
                        self.trigger('change',  media);
                        Notify.success('文件上传成功！');
                        self.trigger('fileinfo.fetching');
                        if (btnData.fileinfoUrl) {
                            $.get($btn.data('fileinfoUrl'), {key:response.hashId}, function(info){
                                self.trigger('fileinfo.fetched', info);
                            }, 'json');
                        } else {
                            self.trigger('fileinfo.fetched', {});
                        }
                    }, 'json');
                } else {
                    var media = self._convertFileToMedia(response);
                    self.trigger('change',  media);
                    self.trigger('fileinfo.fetched', {});
                    Notify.success('文件上传成功！');
                }
            });

            uploader.bind('Error', function(uploader) {
                Notify.danger('文件上传失败，请重试！');
            });

            uploader.init();

            return uploader;
        },

        _createSWFUpload: function($btn, progressbar) {
            var self = this;

            var settings = $.extend({}, {
                upload_url : $btn.data('url'),
                post_params : {
                    "key" : $btn.data('key'),
                    "token" : $btn.data('token'),
                    "x:filepath": $btn.data('filepath'),
                    "x:convertKey": $btn.data('convertKey')
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

                upload_success_handler: function(file, serverData) {
                    progressbar.setComplete().hide();
                    serverData = $.parseJSON(serverData);

                    if ($btn.data('callback')) {
                        $.post($btn.data('callback'), serverData, function(response) {
                            var media = self._convertFileToMedia(response);
                            self.trigger('change',  media);
                            Notify.success('文件上传成功！');
                            self.trigger('fileinfo.fetching');
                            $.get($btn.data('fileinfoUrl'), {key:$btn.data('key')}, function(info){
                                self.trigger('fileinfo.fetched', info);
                            }, 'json');
                        }, 'json');
                    } else {
                        var media = self._convertFileToMedia(serverData);
                        self.trigger('change',  media);
                        self.trigger('fileinfo.fetched', {});
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

    module.exports = FileChooser;
});