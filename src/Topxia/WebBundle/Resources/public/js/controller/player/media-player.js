define(function(require, exports, module) {

    var Widget = require('widget');
    var Notify = require('common/bootstrap-notify');
    var Messenger = require('./messenger');

    var MediaPlayer = Widget.extend({
        attrs: {
        	
        },

        events: {
        },

        //events
        _onBeforePlay: function() {

        },
        _onTimeChange: function(e) {

        },
        _onEnded: function(e) {

        },

        //method
        play: function() {

        },

        seek: function() {

        },

        isPlaying: function(){

        },

        setup: function() {
            var messenger = new Messenger({
                name: 'parent',
                project: 'PlayerProject',
                type: 'child'
            });

            this.set("messenger", messenger);

            this.on('ended', function(e){
                this._onEnded(e);
                this.get("messenger").sendToParent("ended", {"success":"true"});
            });

            this.on('timechange', function(e){
                this._onTimeChange(e);
            });

            messenger.sendToParent("inited", {});
        },  

        destroy: function(){

        }

    });

    module.exports = MediaPlayer;
});