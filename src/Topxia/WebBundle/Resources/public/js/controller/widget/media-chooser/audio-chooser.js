define(function(require, exports, module) {

    var BaseChooser = require('./base-chooser-2');
    require('jquery.nicescroll');

    var AudioChooser = BaseChooser.extend({
    	attrs: {
    		uploaderSettings: {
                file_types : "*.mp3",
                file_size_limit : "100 MB",
                file_types_description: "音频文件"
    		}
    	},
        
        setup: function() {
            AudioChooser.superclass.setup.call(this);
            $('#disk-browser-audio').niceScroll({cursorcolor:"#CCC",cursorwidth:"10px"});
        }

    });

    module.exports = AudioChooser;

});


