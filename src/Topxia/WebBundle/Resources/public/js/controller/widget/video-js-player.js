define(function(require, exports, module) {

    var Widget = require('widget');
    require("video-player");

    var VideoPlayer = Widget.extend({
    	attrs: {
            fingertext: '',
            watermark: '',
            url: '',
            dynamicSource: ''
        },

        events: {},

        setup: function() {
        	var elementId = this.element.attr("id");
        	var self = this;
        	$.get(self.get('url'), function(playlist) {

        		var plugins = {};

        		if(self.get('fingertext') != '') {
        			plugins = $.extend(plugins, {
        				fingerprint: {
	                        html: self.get('fingertext'),
	                        duration: 5000
                     	}
                 	});
        		}

        		if(self.get('watermark') != '') {
        			plugins = $.extend(plugins, {
        				watermark: {
							file: self.get('watermark'),
							xpos: 50,
							ypos: 50,
							xrepeat: 0,
							opacity: 0.5,
						}
                 	});
        		}

                var player = videojs(elementId, {
					techOrder: ["flash", "html5"],
					controls: true,
					autoplay: false,
					preload: 'none',
					language: 'zh-CN',
					plugins: plugins
                });

                player.ready(function() {
                    $.each(playlist, function(i, source) {
                    	player.options().sources.push({'type': 'video/mp4', 'src': source.src, 'data-res': source.name, 'data-level': source.level});
                    });

                    player.resolutionSelector({
                    	default_res : "HD,SD",
                    	dynamic_source : self.get('dynamicSource')
                    });

                });

                player.on('loadedmetadata', function(){
                	self.trigger("beforePlay", player);
                    player.play();
                });

                player.on("timeupdate", function(){
                	self.trigger("timeupdate", player);
                });

                player.on("ended", function(){
                	self.trigger("ended", player);
                });

                self.set('player', player);

            }, 'json');

        },

        destroy: function() {
        	this.get("player").dispose();
        }
    });

    module.exports = VideoPlayer;
});