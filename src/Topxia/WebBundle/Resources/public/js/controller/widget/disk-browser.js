define(function(require, exports, module) {

    var Widget = require('widget');

    var DiskBrowser = Widget.extend({
        attrs: {
            url: null,
            files: []
        },
        _inited: false,

        events: {
            'click .disk-browser-file-list-item': 'onSelectFile'
        },

        setup: function() {
            this._readAttrFromData();
        },

        show: function() {

            if (this._inited) {
                return ;
            }
            _inited = true;

            if (!this.element.hasClass('disk-browser')) {
                this.element.addClass('disk-browser');
            }

            var self = this;

            $.get(this.get('url'), function(files) {
                            
                if (files.length > 0) {
                    var html = '<ul class="disk-browser-file-list">';
                    $.each(files, function(i, file){
                        html += '<li class="disk-browser-file-list-item clearfix" data-index="' + i + '">';
                        html += '<span class="filename">' + file.filename + '</span>';
                        html += '<span class="filesize">' + file.size + '</span>';
                        html += '<span class="filetime">' + file.updatedTime + '</span>';
                        html += '</li>';
                    });
                    html += '</ul>';
                    self.element.html(html);
                    self.set('files', files);
                } else {
                    var message = self.element.data('empty');
                    if (message) {
                        self.element.html('<div class="empty">' + message + '</div>');
                    }
                }

            }, 'json');

            return this;
        },

        onSelectFile: function(e) {
            var $file = $(e.currentTarget);
            var file = this.get('files')[$file.data('index')];
            this.trigger('select', file);
        },

        _readAttrFromData: function() {
            if (!this.get('url')) {
                this.set('url', this.element.data('url'));
            }
        }
    });

    module.exports = DiskBrowser;
});