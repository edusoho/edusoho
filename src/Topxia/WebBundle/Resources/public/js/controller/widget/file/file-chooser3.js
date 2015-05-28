define(function(require, exports, module) {

    var plupload = require('plupload');
    var Widget = require('widget');
    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');

    if ($("div .file-browser-list-container").length > 0) {
        var MaterialFileBrowser = require('../file/file-browser-material-lib');
    }
    var CourseFileBrowser = require('../file/file-browser');

    var FileChooser = Widget.extend({
        attrs: {
            choosed: null,
            uploader: null,
            progressbar: null,
            uploaderSettings: {}
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
            } else {
                this.open();
            }
        },

        open: function() {
            this.show();
            this.$(".file-chooser-bar").hide();
            this.$(".file-chooser-main").show();
            this.$(".file-chooser-uploader-tab").tab('show');
            return this;
        },

        show: function() {
            this.element.show();
            return this;
        },

        close: function() {
            if (this.$('.file-chooser-uploader-tab').parent().hasClass('active')) {
                this._destoryUploader();
                this.$('.file-chooser-uploader-tab').parent().removeClass('active');
            }
            
            this.get('progressbar').reset().hide();
            this.element.find(".file-chooser-main").hide();
            this.element.find(".file-chooser-bar").show();
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
                    self._createUploader();
                    self.get('progressbar').reset().hide();
                }

                if ($(e.relatedTarget).hasClass('file-chooser-uploader-tab')) {
                    if (self.isUploading()) {
                        return confirm('当前正在上传文件，离开此页面，将自动取消上传。您真的要离开吗？');
                    }
                    self._destoryUploader();
                }

                if ($(e.target).hasClass('file-chooser-link-tab')) {
                    self._initLinkPane();
                }

                if ($(e.relatedTarget).hasClass('file-chooser-link-tab')) {
                    self._destoryLinkPane();
                }


            });
        },

        _initFileBrowser: function() {
            var self = this;

            if ($("div .file-browser-list-container").length > 0) {
                var materialBrowser = new MaterialFileBrowser({
                    element: this.$('[data-role=file-browser]')
                }).show();

                materialBrowser.on('select', function(file) {
                    self.trigger('change', self._convertFileToMedia(file));
                });
            }
            var courseBrowser = new CourseFileBrowser({
                element: this.$('[data-role=course-file-browser]')
            }).show();

            courseBrowser.on('select', function(file) {
                self.trigger('change', self._convertFileToMedia(file));
            });
        },

        _initLinkPane: function() {
            var self = this;


            var validator = Validator.query('#course-material-form');
            if (!validator) {
                validator = new Validator({
                    element: '#course-material-form',
                    autoSubmit: false
                });
            }

            validator.addItem({
                element: '[name="link"]',
                display: '链接地址',
                required: true,
                rule: 'url'
            }).on('itemValidated', function(error, results, $item){
                if (error) {
                    $item.parents('form').find('[name=fileId]').val('');
                    return false;
                }
                $item.parents('form').find('[name=fileId]').val('0');
            });

        },

        _destoryLinkPane: function() {
            Validator.query('#course-material-form').removeItem('[name="link"]');
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
            this.set('progressbar', new UploadProgressBar(this.$('[data-role=progress]')));
        },

        _destoryUploader: function() {
            if (this.get('uploader')) {
                this.get('uploader').destroy();
                this.set('uploader', null);
            }
        },

        _createUploader: function() {
            var self = this,
                $btn = this.$('[data-role=uploader-btn]'),
                progressbar = this.get('progressbar'),
                btnData = $btn.data();

            var uploader = new plupload.Uploader({
                runtimes : 'flash',
                max_file_size: '2gb',
                multi_selection: false,
                browse_button : $btn.attr('id'),
                url : btnData.uploadUrl
            });

            uploader.bind('FilesAdded', function(uploader, files) {
                if (uploader.files.length > 0) {
                    Notify.danger('文件正在上传中，请等待本次上传完毕后，再上传。');
                    uploader.removeFile(files[0]);
                }
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
                uploader.removeFile(file);
                uploader.refresh();
                progressbar.setComplete().hide();
                response = $.parseJSON(response.response);

                if (btnData.callback) {
                    $.post(btnData.callback, response, function(response) {
                        var media = self._convertFileToMedia(response);
                        self.trigger('change',  media);
                        Notify.success('文件上传成功！');
                    }, 'json');
                } else {
                    var media = self._convertFileToMedia(response);
                    self.trigger('change',  media);
                    Notify.success('文件上传成功！');
                }
            });

            uploader.bind('Error', function(uploader) {

                Notify.danger('文件上传失败，请重试！');
            });

            uploader.init();

            this.set('uploader', uploader);

            return uploader;
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