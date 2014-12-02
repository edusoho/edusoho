define(function(require, exports, module) {

    var Overlay = require('overlay');
    var TagChooserOverlay = require('tag-chooser-overlay');
    var TagTreeChooser = require('tag-tree-chooser');

    var TagTreeChooserOverlay = TagChooserOverlay.extend({

        show: function() {
            var overlay = this;
            TagChooserOverlay.superclass.show.call(this);
            if (this._chooser) {
                return ;
            }

            var $chooser = this.$('.tagchooser');

            chooser = new TagTreeChooser({
                element: $chooser,
                sourceUrl: $chooser.data('sourceUrl'),
                queryUrl: $chooser.data('queryUrl'),
                matchUrl: $chooser.data('matchUrl'),
                maxTagNum: overlay.get('maxTagNum'),
                // choosedTags: $("#testpaper-search-form").find('input[name=knowledgeIds]').val().split(','),
                alwaysShow: true
            });

            chooser.on('change', function(tags) {
                overlay.set('height', this.getHeight() + 70);
            });

            this._chooser = chooser;
        }

    });

    module.exports = TagTreeChooserOverlay;

});
