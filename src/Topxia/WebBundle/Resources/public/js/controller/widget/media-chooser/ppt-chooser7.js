define(function(require, exports, module) {

    var BaseChooser = require('./base-chooser-8');
    require('jquery.perfect-scrollbar');
    var Notify = require('common/bootstrap-notify');

    var PPTChooser = BaseChooser.extend({
        attrs: {

        },
        
        setup: function() {
            PPTChooser.superclass.setup.call(this);
            $('#disk-browser-ppt').perfectScrollbar({wheelSpeed:50});
        }

    });

    module.exports = PPTChooser;

});


