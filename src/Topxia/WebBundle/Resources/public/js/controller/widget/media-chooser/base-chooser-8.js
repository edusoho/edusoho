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
        _isUploading: false,


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

        show: function() {
            this.element.show();

            if (this.element.find(".file-chooser-main").css('display') == 'none') {
                return this;
            }

            if (this.element.find(".file-chooser-tabs > li.active").length == 0) {
                this.element.find(".file-chooser-uploader-tab").tab('show');
            }

            if (this.element.find(".file-chooser-uploader-tab").parent().hasClass('active')) {
                this._createUploader();
            }

            return this;
        },

        hide: function() {
            if (this.element.find(".file-chooser-uploader-tab").parent().hasClass('active')) {
                this._destoryUploader();
            }
            this.element.hide();
            return this;
        },

        open: function() {
            this.element.find(".file-chooser-bar").hide();
            this.element.find(".file-chooser-main").show();

            if (this.element.find(".file-chooser-tabs > li.active").length == 0) {
                this.element.find(".file-chooser-uploader-tab").tab('show');
            } else {
                var $activeTab = this.element.find(".file-chooser-tabs > li.active");
                this.element.find(".file-chooser-tabs > li").removeClass('active');
                $activeTab.find("a").tab('show');
            }
            
            return this;
        },

        close: function() {
            this._destoryUploader();
            //this.element.find(".file-chooser-tabs > li.active").removeClass('active');

            this.element.find(".file-chooser-main").hide();
            this.element.find(".file-chooser-bar").show();
            return this;
        },

        isUploading: function() { 
            return this._isUploading;
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
                    if (self.isUploading()) {
                        return confirm('当前正在上传文件，离开此页面，将自动取消上传。您真的要离开吗？');
                    }
                    self._destoryUploader();
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

            uploader.on('file.uploaded', function(file, data, response){
                var item = {
                    id: response.id,
                    status: data.status,
                    source: 'self',
                    name: response.filename,
                    length: parseInt(response.length)
                };

                self.trigger("change", item);
                self._isUploading = false;
            });

            uploader.on('file.uploadStart', function(){
                self._isUploading = true;
            });

            uploader.on('preupload', function(file){
                if(self.getProcess){
                    uploader.set('process', self.getProcess());
                }
            });

            uploader.on('file.remove', function (file) {
                self._isUploading = false;
            });

            this.uploader = uploader;
        },

        _destoryUploader: function() {
            if (!this.uploader) {
                return ;
            }
            this.uploader.destroy();
            this.uploader = null;
        },

        destroy: function() {
            this._destoryUploader();
            BaseChooser.superclass.destroy.call(this);
        }

    });

    module.exports = BaseChooser;
});