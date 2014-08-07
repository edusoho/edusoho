define(function(require, exports, module) {

    var Widget = require('widget');

    var VideoQualitySwitcher = Widget.extend({
        attrs: {
            'videoQuality': 'low',
            'audioQuality': 'low'
        },
        events: {
            'click .edit-btn': 'onClickEditBtn',
            'click .cancel-btn': 'onClickCancelBtn',
            'click .confrim-btn': 'onClickConfrimBtn'
        },

        setup: function() {
            this.makeQualitySwitcherName();
            if (this.element.data('editable') === false) {
                this.$('.edit-btn').hide();
            }
        },

        onClickEditBtn: function() {
            this.$('.edit-btn').hide();
            this.$('.quality-switcher-control').show();
        },

        onClickCancelBtn: function() {
            this.$('.quality-switcher-control').hide();
            this.$('.edit-btn').show();
        },

        onClickConfrimBtn: function() {
            this.makeQualitySwitcherName();
            this.$('.quality-switcher-control').hide();
            this.$('.edit-btn').show();
        },

        makeQualitySwitcherName: function() {
            this.set('videoQuality', this.$('[name=video_quality]:checked').val());
            this.set('audioQuality', this.$('[name=video_audio_quality]:checked').val());

            var qualities = {'low': '流畅', 'normal': '标准', 'high': '高品'};

            var name = qualities[this.get('videoQuality')] + '画质 / ' + qualities[this.get('audioQuality')] + '音质';

            this.$('.quality-switcher-name').html( '(' + name + ')');
        }
    });

    module.exports = VideoQualitySwitcher;

});