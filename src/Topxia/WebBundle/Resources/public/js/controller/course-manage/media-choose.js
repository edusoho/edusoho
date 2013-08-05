define(function(require, exports, module) {

    var Widget = require('widget');
    var Notify = require('common/bootstrap-notify');

    var MediaChoose = Widget.extend({
        _library: null,
        _chooses: null,
        attrs: {
            type: null,
            choosed: null
        },

        events: {
            "click [data-role=trigger]": "open",
            "click .library-item": "chooseLibraryItem",
            'click .import-btn': "importItem",
        },

        setup: function() {
            this._library = {};
            this._chooses = {};
            this.on('change', this.onChanged);

            var choosed = this.get('choosed');
            if (choosed) {
                this.set('type', choosed.type);
                this.trigger('change', choosed);
            }
        },

        open: function() {
            var $element = this.element.find(".media-choose-" + this.get('type'));
            $element.find(".media-choose-bar").hide();
            $element.find(".media-choose-main").show();
            this.trigger('change', null);
        },

        close: function() {
            var $element = this.element.find(".media-choose-" + this.get('type'));
            $element.find(".media-choose-main").hide();
            $element.find(".media-choose-bar").show();
        },

        chooseLibraryItem: function(e) {
            var $item = $(e.currentTarget),
                item = this._library[$item.data('type')][$item.data('index')];

            this.trigger('change', item);
        },

        importItem: function(e) {
            var self = this,
                $btn = $(e.currentTarget),
                url = $btn.parents('.input-group').find('.video-url').val();

            if (url.length ==0) {
                Notify.danger('请输入视频页面地址');
                return false;
            }

            if (!/^[a-zA-z]+:\/\/[^\s]*$/.test(url)) {
                Notify.danger('请输入视频页面地址格式不正确');
                return false;
            }

            $btn.button('loading');

            $.get($btn.data('url'), {url:url}, function(video){
                self.trigger('change', video);
                $btn.parents('.input-group').find('.video-url').val('');
            }, 'json').error(function(jqXHR, textStatus, errorThrown) {
                Notify.danger('读取视频页面信息失败，请检查您的输入的页面地址后重试');
            }).always(function(){
                $btn.button('reset');
            });

            return false;
        },

        onChanged: function(item) {
            var type = this.get('type');
            this._chooses[type] = item;
            if (item) {
                var $element = this.element.find(".media-choose-" + type);
                var html = '';
                if (item.page) {
                    html = '<a href="' + item.page + '" target="_blank">' + item.name + '</a>';
                } else {
                    html = item.name;
                }
                $element.find('[data-role=placeholder]').html(html);
                this.close();
            }
        },

        _onChangeType: function(type) {
            this.element.find('.media-choose-video').hide().end().find('.media-choose-audio').hide();
            this.element.find(".media-choose-" + type).show();
            this._initLibrary();
            this.trigger('change', this._chooses[type]);
        },

        _initLibrary: function() {
            var that = this,
                type = this.get('type');

            if (this._library && this._library[type]) {
                return ;
            }

            $.get(this.element.data('mediaLibraryUrl'), {type:type}, function(medias) {
                that._library[type] = medias;
                var html = "<ul class=\"media-choose-library\">";
                $.each(medias, function(i, item){
                    html += "<li class=\"library-item\"  data-type=\"" + type + "\" data-index=\"" + i + "\">" + item.name + "</li>";
                });
                html += "</ul>";
                that.element.find("#type-" + type + "-library").html(html);
            }, 'json');
        }


    });

    module.exports = MediaChoose;
});