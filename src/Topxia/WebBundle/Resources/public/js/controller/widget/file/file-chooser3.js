define(function(require, exports, module) {

    var plupload = require('plupload');
    var Widget = require('widget');
    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');

    if ($("div .file-browser-list-container").length > 0) {
        var MaterialFileBrowser = require('../file/file-browser-material-lib');
    }
    var CourseFileBrowser = require('../file/file-browser');
    var BatchUploader = require('../../uploader/batch-uploader');

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
            this.$(".file-chooser-bar").find('[data-role=placeholder]').text('');
            this.element.closest('form').find('[name=fileId]').val('');
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
                }

                if ($(e.relatedTarget).hasClass('file-chooser-uploader-tab')) {
                    // if (self.isUploading()) {
                    //     return confirm('当前正在上传文件，离开此页面，将自动取消上传。您真的要离开吗？');
                    // }
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

        _destoryUploader: function() {
            if (!this.uploader) {
                return ;
            }
            this.uploader.destroy();
            this.uploader = null;
        },

        _createUploader: function() {
            if (this.uploader) {
                return ;
            }

            var self = this;
            var $el = this.element.find('.balloon-uploader');
            var uploader = new BatchUploader({
                element: $el,
                initUrl: $el.data('initUrl'),
                finishUrl: $el.data('finishUrl'),
                uploadAuthUrl: $el.data('uploadAuthUrl'),
                multi: false
            });

            uploader.on('file.uploaded', function(file, data,response) {
                var item = {
                    id: response.id,
                    status: 'waiting',
                    source: 'self',
                    name: response.filename,
                    length: parseInt(data.length)
                };

                self.trigger("change", item);
            });

            this.uploader = uploader;
        }

    });

    module.exports = FileChooser;
});