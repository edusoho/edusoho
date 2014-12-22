define(function(require, exports, module) {

    var Overlay = require('overlay');
    var TagChooserOverlay = require('tag-chooser-overlay');
    var TagTreeChooser = require('tag-tree-chooser');

    var TagTreeChooserOverlay = TagChooserOverlay.extend({

        show: function() {
            var overlay = this;
            TagChooserOverlay.superclass.show.call(this);
            overlay.$('.panel').css('visibility', 'hidden');
            if (this._chooser) {
                overlay.$('.panel').css('visibility', 'visible');
                return ;
            }

            var $chooser = this.$('.tagchooser');

            chooser = new TagTreeChooser({
                element: $chooser,
                sourceUrl: $chooser.data('sourceUrl'),
                queryUrl: $chooser.data('queryUrl'),
                matchUrl: $chooser.data('matchUrl'),
                maxTagNum: overlay.get('maxTagNum'),
                choosedTags: overlay.get('choosedTags'),
                alwaysShow: true
            });

            chooser.on('change', function(tags) {
                overlay.$('.panel').css('visibility', 'visible');
                overlay.set('height', this.getHeight() + 70);
            });

            this._chooser = chooser;
        }

    });

    module.exports = TagTreeChooserOverlay;

});
