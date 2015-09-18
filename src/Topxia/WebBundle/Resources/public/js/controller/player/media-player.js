define(function(require, exports, module) {

    var Widget = require('widget');

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

            this.on('ended', function(e){
                this._onEnded(e);
            });

            this.on('timechange', function(e){
                this._onTimeChange(e);
            });
            
        },  

        destroy: function(){

        }

    });

    module.exports = MediaPlayer;
});