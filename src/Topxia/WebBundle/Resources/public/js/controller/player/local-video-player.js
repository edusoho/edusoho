define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var VideoJS = require('video-js');
    var Widget = require('widget');

    var LocalVideoPlayer = Widget.extend({
    	attrs: {
        	hasPlayerError: false,
        	url: ''
        },

        events: {

        },

        setup: function() {
        	
            var techOrder = ['flash','html5'];
            if(this.get("agentInWhiteList")) {
                techOrder = ['html5', 'flash'];
            }

    		var that = this;
    		var player = VideoJS(this.element.attr("id"), {
				techOrder: techOrder,
				autoplay: false,
				loop: false
    		});
			player.dimensions('100%', '100%');
			player.src(this.get("url"));

			player.on('error', function(error){
			    that.set("hasPlayerError", true);
			    var message = '您的浏览器不能播放当前视频。';
			    Notify.danger(message, 60);
			});

			player.on('fullscreenchange', function(e) {
			    if ($(e.target).hasClass('vjs-fullscreen')) {
			        $("#site-navbar").hide();
			    }
			});

			player.on('ended', function(e){
                that._onEnded(e);
				that.trigger('ended', e);
			});

			player.on('timeupdate', function(e){
				that.trigger('timechange', e);
			});

			player.on('loadedmetadata' ,function(e){
				that.trigger('ready', e);
			});

            player.on("play", function(e){
                that.trigger("playing", e);
            });

            player.on("pause", function(e){
                that.trigger("paused", e);
            });

			this.set("player", player);

			window.player = this;

			LocalVideoPlayer.superclass.setup.call(this);
    	},

        checkHtml5: function() {
            if (window.applicationCache) {
                return true;
            } else {
                return false;
            }
        },
    	
    	play: function(){
    		this.get("player").play();
    	},

        _onEnded: function(e) {
        	if (this.get("hasPlayerError")) {
		        return ;
		    }
		    var player = this.get("player");
			player.currentTime(0);
			/* 播放器重置时间后马上暂停没用, 延时100毫秒再执行暂停 */
			var _ = require('underscore');
			_.delay(_.bind(player.pause, player), 100);
        },

        getCurrentTime: function() {
        	return this.get("player").currentTime();
        },

        getDuration: function() {
        	return this.get("player").duration();
        },

        setCurrentTime: function(time) {
			this.get("player").currentTime(time);
			return this;
        },

		replay: function () {
			this.setCurrentTime(0).play();
			return this;
		},

        isPlaying: function() {
        	return !this.get("player").paused();
        },

        destroy: function() {
        	this.get("player").dispose();
        }
    });

    module.exports = LocalVideoPlayer;

});