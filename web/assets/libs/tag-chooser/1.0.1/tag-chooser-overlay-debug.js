define(function(require, exports, module) {

    var Overlay = require('overlay');
    var TagChooser = require('tag-chooser');

    var TagChooserOverlay = Overlay.extend({

        attrs: {
            trigger: null,
            triggerType: 'click',
            triggerDefaultName: '全选',
            maxTagNum: 10
        },

        _chooser: null,

        events: {
            'click .tag-search-confrim': '_onClickConfirm',
            'click .tag-search-cancel': '_onClickCancel'
        },

        setup: function() {
            var overlay = this;
            $(this.get('trigger')).on(this.get('triggerType'), function() {
                overlay.show();
            });
        },

        show: function() {
            var overlay = this;
            TagChooserOverlay.superclass.show.call(this);
            if (this._chooser) {
                this._chooser.showDropdown();
                return ;
            }

            var $chooser = this.$('.tagchooser');

            chooser = new TagChooser({
                element: $chooser,
                sourceUrl: $chooser.data('sourceUrl'),
                queryUrl: $chooser.data('queryUrl'),
                matchUrl: $chooser.data('matchUrl'),
                maxTagNum: overlay.get('maxTagNum'),
                choosedTags: overlay.get('choosedTags'),
                alwaysShow: true
            });

            chooser.on('change', function(tags) {
                overlay.set('height', this.getHeight() + 70);
            });

            this._chooser = chooser;
        },

        _onClickConfirm: function() {
            this.hide();
            var tags = this._chooser.get('choosedTags');
            var tagNames = [];
            var tagIds = [];
            $.each(tags, function(i, tag) {
                tagNames.push(tag.name);
                tagIds.push(tag.id);
            });
            var btnText = tagNames.length >0 ? tagNames.join(' ') : this.get('triggerDefaultName');
            $(this.get('trigger')).text(btnText);
            this.trigger('change', tags, tagIds);
        },

        _onClickCancel: function() {
            this.hide();
        }

    });

    module.exports = TagChooserOverlay;

});
