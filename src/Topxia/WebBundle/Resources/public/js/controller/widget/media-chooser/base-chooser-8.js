define(function(require, exports, module) {

    var Widget = require('widget');
    var Notify = require('common/bootstrap-notify');

    var BatchUploader = require('../../uploader/batch-uploader');
    
    if ($("div .file-browser-list-container").length > 0) {
        var MaterialFileBrowser = require('../file/file-browser-material-lib');
    }

    var CourseFileBrowser = require('../file/file-browser');
	

    var BaseChooser = Widget.extend({
        uploader: null,

        attrs: {
            choosed: null,
        },

        events: {
            "click [data-role=trigger]": "open"
        },

        setup: function() {
            this._chooses = {};
            this.on('change', this.onChanged);

            this._initTabs();
            this.FileBrowser();

            var choosed = this.get('choosed');
            if (choosed) {
                this.trigger('change', choosed);
            }

        },

        open: function() {
            this.element.find(".file-chooser-bar").hide();
            this.element.find(".file-chooser-main").show();
            return this;
        },

        show: function() {
            console.log('show chooser');
            this.element.show();
            this._initUploader();
            return this;
        },

        close: function() {
            this.element.find(".file-chooser-main").hide();
            this.element.find(".file-chooser-bar").show();
            if (this.get('uploaderProgressbar')) {
                this.get('uploaderProgressbar').reset().hide();
            }
            return this;
        },

        hide: function() {
            this.element.hide();
            return this;
        },

        isUploading: function() {
            if (!this.get('uploaderProgressbar')) {
                return false;
            }
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
                    if (self.get('uploaderProgressbar')) {
                        self.get('uploaderProgressbar').reset().hide();
                    }
                    
                }

                if ($(e.relatedTarget).hasClass('file-chooser-uploader-tab')) {
                    if (self.isUploading()) {
                        var result = confirm('当前正在上传文件，离开此页面，将自动取消上传。您真的要离开吗？');
                        if(result){
                            self.get("uploadPanel").destroy();
                        }
                        return result;
                    }
                }
            });
        },

        FileBrowser: function() {
            var self = this;

            if ($("div .file-browser-list-container").length > 0) {
                    var materialBrowser = new MaterialFileBrowser({
                        element: this.$('[data-role=file-browser]')
                    }).show();

                    materialBrowser.on('select', function(file) {
                        var media = {
                            id: file.id,
                            status: file.convertStatus,
                            source: 'self',
                            name: file.filename,
                            length: file.length
                        };
                        self.trigger('change', media);
                    });
            }
            
            var courseBrowser = new CourseFileBrowser({
                element: this.$('[data-role=course-file-browser]')
            }).show();

            courseBrowser.on('select', function(file) {
                var media = {
                    id: file.id,
                    status: file.convertStatus,
                    source: 'self',
                    name: file.filename,
                    length: file.length
                };
                self.trigger('change', media);
            });
        },

        _initUploader: function() {
            if (this.uploader) {
                return ;
            }

            console.log('init pane');
            var self = this;
            var $el = this.element.find('.balloon-uploader');
            var uploader = new BatchUploader({
                element: $el,
                initUrl: $el.data('initUrl'),
                finishUrl: $el.data('finishUrl')
            });

            uploader.on('file.uploaded', function(file, data){
                console.log('file.uploaded', file, data);
                var item = {
                    id: file.outerId,
                    status: 'waiting',
                    source: 'self',
                    name: file.name,
                    length: parseInt(data.length)
                };

                self.trigger("change", item);
            });

            uploader.on('file.queued', function(file) {
                this.uploader.upload();
            });


            this.uploader = uploader;
        },

        destroy: function() {
            if (!this.uploader) {
                return ;
            }
            this.uploader.destroy();
        }

    });

    module.exports = BaseChooser;
});