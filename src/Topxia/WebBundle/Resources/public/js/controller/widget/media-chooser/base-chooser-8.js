define(function(require, exports, module) {

    var Widget = require('widget');
    var UploadPanel = require('edusoho.uploadpanel');
    var Notify = require('common/bootstrap-notify');
    
    if ($("div .file-browser-list-container").length > 0) {
        var MaterialFileBrowser = require('../file/file-browser-material-lib');
    }

    var CourseFileBrowser = require('../file/file-browser');
	

    var BaseChooser = Widget.extend({
        attrs: {
            choosed: null,
            uploader: null,
            uploaderSettings: {},
            preUpload: null,
            uploadPanel: null
        },

        events: {
            "click [data-role=trigger]": "open"
        },

        setup: function() {
            this._chooses = {};
            this.on('change', this.onChanged);
            this.on('preUpload', this.get("preUpload"));

            this._initTabs();
            this.FileBrowser();
            this._initUploadPane();

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
            this.element.show();
            return this;
        },

        close: function() {
            this.element.find(".file-chooser-main").hide();
            this.element.find(".file-chooser-bar").show();
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
                    self.get('uploaderProgressbar').reset().hide();
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
                        self.trigger('change', self.get("uploadPanel")._convertFileToMedia(file));
                    });
            }
            
            var courseBrowser = new CourseFileBrowser({
                element: this.$('[data-role=course-file-browser]')
            }).show();

            courseBrowser.on('select', function(file) {
                self.trigger('change', self.get("uploadPanel")._convertFileToMedia(file));
            });
        },

        _initUploadPane: function(){
            var self = this;
            var uploadPanel = new UploadPanel({
                element: this.element,
                uploaderSettings: this.get("uploaderSettings")
            });
            uploadPanel.on("preUpload", function(uploader, file){
                self.trigger("preUpload", uploader, file);
            });
            uploadPanel.on("change", function(item){
                self.trigger("change",item);
            });
            this.set("uploadPanel", uploadPanel);
            this.set("uploaderProgressbar", uploadPanel.get("uploaderProgressbar"));
        },
        destroy: function(){
            this.get("uploadPanel").destroy();
        }

    });

    module.exports = BaseChooser;
});