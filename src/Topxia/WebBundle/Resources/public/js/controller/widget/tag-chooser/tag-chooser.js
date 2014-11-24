define(function(require, exports, module) {

    var Widget = require('widget');
    var Overlay = require('overlay');
    var Autocomplete = require('autocomplete');

    var TagChooser = Widget.extend({
        attrs: {
            multi: true,
            choosedTags: []
        },

        _tagOverlay: null,

        events: {
            'click .dropdown' : 'onDropdown',
            'click .tag-cancel': 'onTagCancel',
            'click .tag-confirm': 'onTagConfirm',
            'click .tag-item': 'onTagItem',
            'click .tag-remove': 'onTagRemove'
        },

        setup: function() {

            var overlayY = this.$('.input-group').height();
            var overlayWidth = this.$('.input-group').width();

            var overlay = new Overlay({
                element: this.$('.tag-overlay'),
                width: overlayWidth,
                height: 300,
                align: {
                    baseElement: this.$('.input-group'),
                    baseXY: [0, overlayY]
                }

            });

            overlay._blurHide([overlay.element, this.$('.dropdown')]);

            this._tagOverlay = overlay;
        },

        onDropdown: function(e) {
            if (this._tagOverlay.get('visible')) {
                this._tagOverlay.hide();
            } else {
                this._initData();
                this._tagOverlay.show();
            }
        },

        onTagCancel: function(e) {
            this._tagOverlay.hide();
        },

        onTagConfirm: function(e) {
            var choosedTags = [];
            this.$('.tag-overlay').find('.tag-item-choosed').each(function(index, item) {
                var $item = $(item);
                choosedTags.push($item.data());
            });

            this.set('choosedTags', choosedTags);
            this.trigger('choosed', choosedTags);
            this._tagOverlay.hide();
        },

        onTagRemove: function (e) {
            $(e.currentTarget).parents('.choosed-tag').remove();

            var choosedTags = [];
            this.$('.tags-choosed').find('.choosed-tag').each(function(index, item) {
                choosedTags.push($(item).data());
            });
            this.set('choosedTags', choosedTags);
        },

        onTagItem: function(e) {
            var $item = $(e.currentTarget);

            if (this.get('multi')) {
                if ($item.hasClass('tag-item-choosed')) {
                    $item.removeClass('tag-item-choosed');
                } else {
                    $item.addClass('tag-item-choosed');
                }
            } else {
                this.element.find('.tag-item-choosed').removeClass('tag-item-choosed');
                $item.addClass('tag-item-choosed');
            }

        },

        _onChangeChoosedTags: function(tags) {
            var $tags = this.$('.tags-choosed').empty();

            var $tagTemplate = this.$('.choosed-tag-template');

            $.each(tags, function(index, tag) {
                var $tag = $tagTemplate.clone().removeClass('choosed-tag-template');
                $tag.data(tag);
                $tag.find('.tag-name-placeholder').html(tag.name);
                $tags.append($tag);
            });
        },

        _initData: function() {
            var self = this;
            self.$('.tag-overlay').find('.tag-item-choosed').removeClass('tag-item-choosed');
            $.each(self.get('choosedTags'), function(index, tag) {
                self.$('.tag-overlay').find('.tag-item-' + tag.id).addClass('tag-item-choosed');
            });
        }

    });

    module.exports = TagChooser;
});