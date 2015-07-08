define(function(require, exports, module) {

    var Widget = require('widget');
    require("video-player");
    var Cookie = require('cookie');

    var VideoPlayer = Widget.extend({
    	attrs: {
            fingerprint: '',
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

                if(self.get('fingerprint') != '') {
                    plugins = $.extend(plugins, {
                        fingerprint: {
                            html: self.get('fingerprint'),
                            duration: 5000
                        }
                    })
                }

                var player = videojs(elementId, {
					techOrder: ["flash", "html5"],
					controls: true,
					autoplay: true,
					preload: 'none',
					language: 'zh-CN',
					plugins: plugins,
                });

                player.ready(function() {
                    var resArray = [];
                    $.each(playlist, function(i, source) {
                        resArray.push(source.name);
                    	player.options().sources.push({'type': 'video/mp4', 'src': source.src, 'data-res': source.name, 'data-level': source.level});
                    });
                    var currentRes = Cookie.get("currentRes");
                    if(currentRes == undefined){
                        currentRes = resArray.join(",");
                    }
                    player.resolutionSelector({
                    	default_res : currentRes,
                    	dynamic_source : self.get('url')
                    });

                });

                player.on('changeRes', function() {
                    Cookie.set("currentRes", player.getCurrentRes());
                });

                player.on('loadedmetadata', function(){
                	self.trigger("beforePlay", player);
                    player.player();
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

        paused: function() {
            return this.get("player").paused();
        },

        destroy: function() {
        	this.get("player").dispose();
        }
    });

    module.exports = VideoPlayer;
});