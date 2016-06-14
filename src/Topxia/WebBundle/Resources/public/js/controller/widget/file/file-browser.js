define(function(require, exports, module) {

    var Widget = require('widget');

    var FileBrowser = Widget.extend({
        attrs: {
            url: null,
            files: []
        },
        _inited: false,

        events: {
            'click .file-browser-item': 'onSelectFile'
        },

        setup: function() {
            this._readAttrFromData();
        },

        show: function() {

            if (this._inited) {
                return ;
            }
            _inited = true;

            if (!this.element.hasClass('file-browser')) {
                this.element.addClass('file-browser');
            }

            var self = this;

            $.get(this.get('url'), function(response) {
                self.refreshFileList.call(self, response.files, response.paginator);
            }, 'json');

            this.element.on('click.switch-page', '.js-switch-page', $.proxy(this._onSwitchPage, this));

            return this;
        },

        onSelectFile: function(e) {
            var $file = $(e.currentTarget);
            var file = this.get('files')[$file.data('index')];
            this.trigger('select', file);
        },

        _onSwitchPage: function (event) {
            var self = this;
            var url = $(event.target).data('url');

            if(url === undefined){
                return;
            }

            $.get(url, function (response) {
                self.refreshFileList.call(self, response.files, response.paginator);
            }, 'json');
        },

        refreshFileList: function (files, paginator) {
            
            if (files.length > 0) {
                var html = '<ul class="file-browser-list">';
                $.each(files, function(i, file){
                    html += '<li class="file-browser-item clearfix" data-index="' + i + '">';
                    html += '<span class="filename">' + file.filename + '</span>';
                    html += '<span class="filesize">' + file.fileSize + '</span>';
                    html += '<span class="filetime">' + file.updatedTime + '</span>';
                    html += '</li>';
                });
                html += '</ul>';

                if(!$.isEmptyObject(paginator)){
                    html += '<nav class="text-center">';
                    html += '<ul class="pagination">';
                    if (paginator.currentPage != paginator.firstPage) {
                        html += '<li><a href="javascript:;" class="js-switch-page" data-url="' + paginator.firstPageUrl + '">首页</a></li>'
                        html += '<li><a class="es-icon es-icon-chevronleft js-switch-page" data-url="' + paginator.previousPageUrl + '"></a></li>';
                    }   


                    paginator.pageUrls.length > 1 && $.each(paginator.pageUrls, function (index, url) {
                        var page = index + 1;
                        if (page == paginator.currentPage) {
                            html += '<li class="active"><a href="javascript:;" class="js-switch-page" data-url="' + url + '">' +  page + '</a></li>';
                        } else {
                            html += '<li ><a href="javascript:;" class="js-switch-page" data-url="' + url + '">' + page + '</a></li>';
                        }
                    });

                    if (paginator.currentPage != paginator.lastPage) {
                        html += '<li><a class="es-icon es-icon-chevronright js-switch-page" data-url="' + paginator.nextPageUrl + '"></a></li>';
                        html += '<li><a href="javascript:;" class="js-switch-page" data-url="' + paginator.lastPageUrl + '">尾页</a></li>';
                    }
                    html += '</ul>';
                    html += '</nav>';
                }

                this.element.html(html);
                this.set('files', files);
            } else {
                var message = this.element.data('empty');
                if (message) {
                    this.element.html('<div class="empty">' + message + '</div>');
                }
            }
        },

        _readAttrFromData: function() {
            if (!this.get('url')) {
                this.set('url', this.element.data('url'));
            }
        }
    });

    module.exports = FileBrowser;
});