define(function(require, exports, module) {

    var BaseChooser = require('./base-chooser-8');
    require('jquery.perfect-scrollbar');
    var Notify = require('common/bootstrap-notify');

    var AudioChooser = BaseChooser.extend({
    	attrs: {

    	},
        
        setup: function() {
            AudioChooser.superclass.setup.call(this);
            $('#disk-browser-audio').perfectScrollbar({wheelSpeed:50});
        }

    });

    module.exports = AudioChooser;

});


