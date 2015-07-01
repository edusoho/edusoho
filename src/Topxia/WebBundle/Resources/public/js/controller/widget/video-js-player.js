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

        		if(this.get('fingertext') != '') {
        			plugins = $.extend(plugins, {
        				fingerprint: {
	                        html: this.get('fingertext'),
	                        duration: 5000
                     	}
                 	});
        		}

        		if(this.get('watermark') != '') {
        			plugins = $.extend(plugins, {
        				watermark: {
							file: this.get('watermark'),
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

                self.set('player', player);

            }, 'json');

        }
    });

    module.exports = VideoPlayer;
});